<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSalaryPaymentRequest;
use App\Http\Requests\UpdateSalaryPaymentRequest;
use App\Models\Branch;
use App\Models\SalaryPayment;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SalaryPaymentsController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('salary_payment_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $salaryPayments = SalaryPayment::with([
            'branch',
            'user',
            'teacher.user',
            'staff.user',
            'paidBy',
        ]);

        if (auth()->user()->is_admin) {
            // all
        } elseif ($this->isTeacher() || $this->isStaff()) {
            $salaryPayments->where('user_id', auth()->id());
        } elseif ($this->isStudent()) {
            $salaryPayments->whereRaw('1 = 0');
        } else {
            $branchId = $this->getUserBranchId();

            $branchId ? $salaryPayments->where('branch_id', $branchId) : $salaryPayments->whereRaw('1 = 0');
        }

        $filters = $request->only(['employee_type', 'salary_type', 'branch_id', 'salary_month', 'payment_status', 'teacher_id', 'staff_id']);

        if (! empty($filters['employee_type'])) {
            $salaryPayments->where('employee_type', $filters['employee_type']);
        }

        if (! empty($filters['salary_type'])) {
            $salaryPayments->where('salary_type', $filters['salary_type']);
        }

        if (! empty($filters['branch_id'])) {
            $salaryPayments->where('branch_id', $filters['branch_id']);
        }

        if (! empty($filters['salary_month'])) {
            $salaryPayments->where('salary_month', $filters['salary_month']);
        }

        if (! empty($filters['payment_status'])) {
            $salaryPayments->where('payment_status', $filters['payment_status']);
        }

        if (! empty($filters['teacher_id'])) {
            $salaryPayments->where('teacher_id', $filters['teacher_id']);
        }

        if (! empty($filters['staff_id'])) {
            $salaryPayments->where('staff_id', $filters['staff_id']);
        }

        $salaryPayments = $salaryPayments->latest()->get();

        $branchId = $this->getUserBranchId();

        $branches = Branch::where('status', 'active')
            ->when(! auth()->user()->is_admin, fn ($q) => $branchId ? $q->where('id', $branchId) : $q->whereRaw('1 = 0'))
            ->pluck('name', 'id')
            ->prepend('All Branches', '');

        $teacherFilterOptions = Teacher::with('user')
            ->when(! auth()->user()->is_admin, fn ($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereRaw('1 = 0'))
            ->get()
            ->mapWithKeys(fn ($teacher) => [$teacher->id => $teacher->user->name ?? 'Teacher #' . $teacher->id])
            ->prepend('All Teachers', '');

        $staffFilterOptions = Staff::with('user')
            ->when(! auth()->user()->is_admin, fn ($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereRaw('1 = 0'))
            ->get()
            ->mapWithKeys(fn ($member) => [$member->id => $member->user->name ?? 'Staff #' . $member->id])
            ->prepend('All Staff', '');

        return view('admin.salaryPayments.index', compact('salaryPayments', 'filters', 'branches', 'teacherFilterOptions', 'staffFilterOptions'));
    }

    public function create()
    {
        abort_if(Gate::denies('salary_payment_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent() || $this->isStaff(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branchId = $this->getUserBranchId();

        $branches = Branch::where('status', 'active')
            ->when(! auth()->user()->is_admin, fn ($q) => $branchId ? $q->where('id', $branchId) : $q->whereRaw('1 = 0'))
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $teachers = Teacher::with('user')
            ->when(! auth()->user()->is_admin, fn ($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereRaw('1 = 0'))
            ->get()
            ->mapWithKeys(function ($teacher) {
                return [$teacher->id => ($teacher->user->name ?? 'Teacher') . ' - ₹' . number_format($teacher->salary, 0)];
            })
            ->prepend(trans('global.pleaseSelect'), '');

        $staff = Staff::with('user')
            ->when(! auth()->user()->is_admin, fn ($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereRaw('1 = 0'))
            ->get()
            ->mapWithKeys(function ($member) {
                return [$member->id => ($member->user->name ?? 'Staff') . ' - ' . ($member->designation ?? 'Staff') . ' - ₹' . number_format($member->salary, 0)];
            })
            ->prepend(trans('global.pleaseSelect'), '');

        $users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $paymentModes = $this->paymentModes();

        return view('admin.salaryPayments.create', compact('branches', 'teachers', 'staff', 'users', 'paymentModes'));
    }

    public function store(StoreSalaryPaymentRequest $request)
    {
        abort_if($this->isTeacher() || $this->isStudent() || $this->isStaff(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $this->prepareSalaryData($request->validated());

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            abort_if(($data['branch_id'] ?? null) != $branchId, Response::HTTP_FORBIDDEN, 'Invalid branch.');

            $data['branch_id'] = $branchId;
        }

        SalaryPayment::create($data);

        return redirect()->route('admin.salary-payments.index')->with('message', 'Salary payment created successfully.');
    }

    public function show(SalaryPayment $salaryPayment)
    {
        abort_if(Gate::denies('salary_payment_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkSalaryAccess($salaryPayment);

        $salaryPayment->load([
            'branch',
            'user',
            'teacher.user',
            'staff.user',
            'paidBy',
        ]);

        return view('admin.salaryPayments.show', compact('salaryPayment'));
    }

    public function edit(SalaryPayment $salaryPayment)
    {
        abort_if(Gate::denies('salary_payment_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent() || $this->isStaff(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkSalaryAccess($salaryPayment);

        $branchId = $this->getUserBranchId();

        $branches = Branch::where('status', 'active')
            ->when(! auth()->user()->is_admin, fn ($q) => $branchId ? $q->where('id', $branchId) : $q->whereRaw('1 = 0'))
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $teachers = Teacher::with('user')
            ->when(! auth()->user()->is_admin, fn ($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereRaw('1 = 0'))
            ->get()
            ->mapWithKeys(function ($teacher) {
                return [$teacher->id => ($teacher->user->name ?? 'Teacher') . ' - ₹' . number_format($teacher->salary, 0)];
            })
            ->prepend(trans('global.pleaseSelect'), '');

        $staff = Staff::with('user')
            ->when(! auth()->user()->is_admin, fn ($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereRaw('1 = 0'))
            ->get()
            ->mapWithKeys(function ($member) {
                return [$member->id => ($member->user->name ?? 'Staff') . ' - ' . ($member->designation ?? 'Staff') . ' - ₹' . number_format($member->salary, 0)];
            })
            ->prepend(trans('global.pleaseSelect'), '');

        $users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $paymentModes = $this->paymentModes();

        $salaryPayment->load([
            'branch',
            'user',
            'teacher.user',
            'staff.user',
            'paidBy',
        ]);

        return view('admin.salaryPayments.edit', compact('salaryPayment', 'branches', 'teachers', 'staff', 'users', 'paymentModes'));
    }

    public function update(UpdateSalaryPaymentRequest $request, SalaryPayment $salaryPayment)
    {
        abort_if($this->isTeacher() || $this->isStudent() || $this->isStaff(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkSalaryAccess($salaryPayment);

        $data = $this->prepareSalaryData($request->validated(), $salaryPayment);

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            abort_if(($data['branch_id'] ?? null) != $branchId, Response::HTTP_FORBIDDEN, 'Invalid branch.');

            $data['branch_id'] = $branchId;
        }

        $salaryPayment->update($data);

        return redirect()->route('admin.salary-payments.index')->with('message', 'Salary payment updated successfully.');
    }

    public function destroy(SalaryPayment $salaryPayment)
    {
        abort_if(Gate::denies('salary_payment_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent() || $this->isStaff(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkSalaryAccess($salaryPayment);

        $salaryPayment->delete();

        return back()->with('message', 'Salary payment deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('salary_payment_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent() || $this->isStaff(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = SalaryPayment::whereIn('id', request('ids'));

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
        }

        $query->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function slip(SalaryPayment $salaryPayment)
    {
        abort_if(Gate::denies('salary_payment_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkSalaryAccess($salaryPayment);

        $salaryPayment->load([
            'branch',
            'user',
            'teacher.user',
            'staff.user',
            'paidBy',
        ]);

        return view('admin.salaryPayments.slip', compact('salaryPayment'));
    }

    private function checkSalaryAccess(SalaryPayment $salaryPayment): void
    {
        if (auth()->user()->is_admin) {
            return;
        }

        if ($this->isTeacher() || $this->isStaff()) {
            abort_if($salaryPayment->user_id != auth()->id(), Response::HTTP_FORBIDDEN, '403 Forbidden');

            return;
        }

        $branchId = $this->getUserBranchId();

        abort_if(! $branchId || $salaryPayment->branch_id != $branchId, Response::HTTP_FORBIDDEN, '403 Forbidden');
    }

    private function prepareSalaryData(array $data, SalaryPayment $salaryPayment = null): array
    {
        $basicSalary = (float) ($data['basic_salary'] ?? 0);
        $bonus = (float) ($data['bonus'] ?? 0);
        $deduction = (float) ($data['deduction'] ?? 0);
        $paidAmount = (float) ($data['paid_amount'] ?? 0);

        $netSalary = max(($basicSalary + $bonus) - $deduction, 0);
        $dueAmount = max($netSalary - $paidAmount, 0);

        $data['bonus'] = $bonus;
        $data['deduction'] = $deduction;
        $data['net_salary'] = $netSalary;
        $data['due_amount'] = $dueAmount;

        if (empty($data['slip_no'])) {
            $data['slip_no'] = $salaryPayment->slip_no ?? $this->generateSlipNo();
        }

        if (($data['payment_status'] ?? null) !== 'cancelled') {
            if ($paidAmount >= $netSalary && $netSalary > 0) {
                $data['payment_status'] = 'paid';
            } elseif ($paidAmount > 0) {
                $data['payment_status'] = 'partial';
            } else {
                $data['payment_status'] = 'due';
            }
        }

        if (empty($data['paid_by_id'])) {
            $data['paid_by_id'] = auth()->id();
        }

        if (empty($data['payment_date'])) {
            $data['payment_date'] = now()->format('Y-m-d');
        }

        if (($data['employee_type'] ?? null) === 'teacher' && ! empty($data['teacher_id'])) {
            $teacher = Teacher::find($data['teacher_id']);

            $data['user_id'] = $teacher->user_id ?? null;
            $data['staff_id'] = null;
            $data['salary_type'] = $teacher->salary_type ?? 'monthly';

            if (empty($data['branch_id'])) {
                $data['branch_id'] = $teacher->branch_id ?? null;
            }
        }

        if (in_array($data['employee_type'] ?? null, ['staff', 'manager']) && ! empty($data['staff_id'])) {
            $staff = Staff::find($data['staff_id']);

            $data['user_id'] = $staff->user_id ?? null;
            $data['teacher_id'] = null;
            $data['salary_type'] = $staff->salary_type ?? 'monthly';

            if (empty($data['branch_id'])) {
                $data['branch_id'] = $staff->branch_id ?? null;
            }
        }

        return $data;
    }

    private function generateSlipNo(): string
    {
        $lastPayment = SalaryPayment::latest('id')->first();
        $nextId = $lastPayment ? $lastPayment->id + 1 : 1;

        return 'SAL-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
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

        return null;
    }

    private function isTeacher(): bool
    {
        return auth()->user()->roles()->where('title', 'Teacher')->exists();
    }

    private function isStaff(): bool
    {
        return auth()->user()->roles()->where('title', 'Staff')->exists();
    }

    private function isStudent(): bool
    {
        return auth()->user()->roles()->where('title', 'Student')->exists();
    }
}