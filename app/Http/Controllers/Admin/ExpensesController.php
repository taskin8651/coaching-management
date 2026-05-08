<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExpensesController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('expense_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $expenses = Expense::with(['branch', 'paidBy']);

        if (auth()->user()->is_admin) {
            // all
        } elseif ($this->isTeacher() || $this->isStudent()) {
            $expenses->whereRaw('1 = 0');
        } else {
            $branchId = $this->getUserBranchId();

            $branchId ? $expenses->where('branch_id', $branchId) : $expenses->whereRaw('1 = 0');
        }

        $expenses = $expenses->latest()->get();

        return view('admin.expenses.index', compact('expenses'));
    }

    public function create()
    {
        abort_if(Gate::denies('expense_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branchId = $this->getUserBranchId();

        $branches = Branch::where('status', 'active')
            ->when(! auth()->user()->is_admin, fn ($q) => $branchId ? $q->where('id', $branchId) : $q->whereRaw('1 = 0'))
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $categories = $this->categories();
        $paymentModes = $this->paymentModes();

        return view('admin.expenses.create', compact('branches', 'users', 'categories', 'paymentModes'));
    }

    public function store(StoreExpenseRequest $request)
    {
        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validated();

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;
        }

        if (empty($data['paid_by_id'])) {
            $data['paid_by_id'] = auth()->id();
        }

        if (empty($data['expense_date'])) {
            $data['expense_date'] = now()->format('Y-m-d');
        }

        Expense::create($data);

        return redirect()->route('admin.expenses.index')->with('message', 'Expense created successfully.');
    }

    public function show(Expense $expense)
    {
        abort_if(Gate::denies('expense_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkExpenseAccess($expense);

        $expense->load(['branch', 'paidBy']);

        return view('admin.expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        abort_if(Gate::denies('expense_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkExpenseAccess($expense);

        $branchId = $this->getUserBranchId();

        $branches = Branch::where('status', 'active')
            ->when(! auth()->user()->is_admin, fn ($q) => $branchId ? $q->where('id', $branchId) : $q->whereRaw('1 = 0'))
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $categories = $this->categories();
        $paymentModes = $this->paymentModes();

        $expense->load(['branch', 'paidBy']);

        return view('admin.expenses.edit', compact('expense', 'branches', 'users', 'categories', 'paymentModes'));
    }

    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkExpenseAccess($expense);

        $data = $request->validated();

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;
        }

        if (empty($data['paid_by_id'])) {
            $data['paid_by_id'] = auth()->id();
        }

        $expense->update($data);

        return redirect()->route('admin.expenses.index')->with('message', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        abort_if(Gate::denies('expense_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkExpenseAccess($expense);

        $expense->delete();

        return back()->with('message', 'Expense deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('expense_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Expense::whereIn('id', request('ids'));

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
        }

        $query->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function checkExpenseAccess(Expense $expense): void
    {
        if (auth()->user()->is_admin) {
            return;
        }

        $branchId = $this->getUserBranchId();

        abort_if(! $branchId || $expense->branch_id != $branchId, Response::HTTP_FORBIDDEN, '403 Forbidden');
    }

    private function getUserBranchId()
    {
        $user = auth()->user();

        if ($user->is_admin) {
            return null;
        }

        $managedBranch = Branch::where('manager_id', $user->id)->first();

        if ($managedBranch) {
            return $managedBranch->id;
        }

        $staff = Staff::where('user_id', $user->id)->first();

        if ($staff) {
            return $staff->branch_id;
        }

        $teacher = Teacher::where('user_id', $user->id)->first();

        if ($teacher) {
            return $teacher->branch_id;
        }

        $student = Student::where('user_id', $user->id)->first();

        if ($student) {
            return $student->branch_id;
        }

        return null;
    }

    private function categories(): array
    {
        return [
            'Rent' => 'Rent',
            'Electricity Bill' => 'Electricity Bill',
            'Internet Bill' => 'Internet Bill',
            'Water Bill' => 'Water Bill',
            'Office Supplies' => 'Office Supplies',
            'Marketing / Advertisement' => 'Marketing / Advertisement',
            'Repair & Maintenance' => 'Repair & Maintenance',
            'Furniture' => 'Furniture',
            'Stationery' => 'Stationery',
            'Cleaning' => 'Cleaning',
            'Transport' => 'Transport',
            'Miscellaneous' => 'Miscellaneous',
        ];
    }

    private function paymentModes(): array
    {
        return [
            'cash' => 'Cash',
            'upi' => 'UPI',
            'bank_transfer' => 'Bank Transfer',
            'cheque' => 'Cheque',
            'card' => 'Card',
            'other' => 'Other',
        ];
    }

    private function isTeacher(): bool
    {
        return auth()->user()->roles()->where('title', 'Teacher')->exists();
    }

    private function isStudent(): bool
    {
        return auth()->user()->roles()->where('title', 'Student')->exists();
    }
}