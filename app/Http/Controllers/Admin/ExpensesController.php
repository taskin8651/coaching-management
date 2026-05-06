<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExpensesController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('expense_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $expenses = Expense::with(['branch', 'paidBy'])->latest()->get();

        return view('admin.expenses.index', compact('expenses'));
    }

    public function create()
    {
        abort_if(Gate::denies('expense_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branches = Branch::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $users = User::pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $categories = $this->categories();
        $paymentModes = $this->paymentModes();

        return view('admin.expenses.create', compact('branches', 'users', 'categories', 'paymentModes'));
    }

    public function store(StoreExpenseRequest $request)
    {
        $data = $request->validated();

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

        $expense->load(['branch', 'paidBy']);

        return view('admin.expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        abort_if(Gate::denies('expense_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branches = Branch::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $users = User::pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $categories = $this->categories();
        $paymentModes = $this->paymentModes();

        $expense->load(['branch', 'paidBy']);

        return view('admin.expenses.edit', compact('expense', 'branches', 'users', 'categories', 'paymentModes'));
    }

    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        $data = $request->validated();

        if (empty($data['paid_by_id'])) {
            $data['paid_by_id'] = auth()->id();
        }

        $expense->update($data);

        return redirect()->route('admin.expenses.index')->with('message', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        abort_if(Gate::denies('expense_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $expense->delete();

        return back()->with('message', 'Expense deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('expense_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Expense::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
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
}