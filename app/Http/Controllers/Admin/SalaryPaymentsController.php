<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSalaryPaymentRequest;
use App\Http\Requests\UpdateSalaryPaymentRequest;
use App\Models\Branch;
use App\Models\SalaryPayment;
use App\Models\Staff;
use App\Models\Teacher;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SalaryPaymentsController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('salary_payment_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $salaryPayments = SalaryPayment::with([
            'branch',
            'user',
            'teacher.user',
            'staff.user',
            'paidBy',
        ])->latest()->get();

        return view('admin.salaryPayments.index', compact('salaryPayments'));
    }

    public function create()
    {
        abort_if(Gate::denies('salary_payment_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branches = Branch::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $teachers = Teacher::with('user')->get()->mapWithKeys(function ($teacher) {
            return [$teacher->id => ($teacher->user->name ?? 'Teacher') . ' - ₹' . number_format($teacher->salary, 0)];
        })->prepend(trans('global.pleaseSelect'), '');

        $staff = Staff::with('user')->get()->mapWithKeys(function ($member) {
            return [$member->id => ($member->user->name ?? 'Staff') . ' - ' . ($member->designation ?? 'Staff') . ' - ₹' . number_format($member->salary, 0)];
        })->prepend(trans('global.pleaseSelect'), '');

        $users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $paymentModes = $this->paymentModes();

        return view('admin.salaryPayments.create', compact('branches', 'teachers', 'staff', 'users', 'paymentModes'));
    }

    public function store(StoreSalaryPaymentRequest $request)
    {
        $data = $this->prepareSalaryData($request->validated());

        SalaryPayment::create($data);

        return redirect()->route('admin.salary-payments.index')->with('message', 'Salary payment created successfully.');
    }

    public function show(SalaryPayment $salaryPayment)
    {
        abort_if(Gate::denies('salary_payment_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

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

        $branches = Branch::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $teachers = Teacher::with('user')->get()->mapWithKeys(function ($teacher) {
            return [$teacher->id => ($teacher->user->name ?? 'Teacher') . ' - ₹' . number_format($teacher->salary, 0)];
        })->prepend(trans('global.pleaseSelect'), '');

        $staff = Staff::with('user')->get()->mapWithKeys(function ($member) {
            return [$member->id => ($member->user->name ?? 'Staff') . ' - ' . ($member->designation ?? 'Staff') . ' - ₹' . number_format($member->salary, 0)];
        })->prepend(trans('global.pleaseSelect'), '');

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
        $data = $this->prepareSalaryData($request->validated(), $salaryPayment);

        $salaryPayment->update($data);

        return redirect()->route('admin.salary-payments.index')->with('message', 'Salary payment updated successfully.');
    }

    public function destroy(SalaryPayment $salaryPayment)
    {
        abort_if(Gate::denies('salary_payment_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $salaryPayment->delete();

        return back()->with('message', 'Salary payment deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('salary_payment_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        SalaryPayment::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function slip(SalaryPayment $salaryPayment)
    {
        abort_if(Gate::denies('salary_payment_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $salaryPayment->load([
            'branch',
            'user',
            'teacher.user',
            'staff.user',
            'paidBy',
        ]);

        return view('admin.salaryPayments.slip', compact('salaryPayment'));
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

        if ($data['employee_type'] === 'teacher' && !empty($data['teacher_id'])) {
            $teacher = Teacher::find($data['teacher_id']);

            $data['user_id'] = $teacher->user_id ?? null;
            $data['staff_id'] = null;

            if (empty($data['branch_id'])) {
                $data['branch_id'] = $teacher->branch_id ?? null;
            }
        }

        if (in_array($data['employee_type'], ['staff', 'manager']) && !empty($data['staff_id'])) {
            $staff = Staff::find($data['staff_id']);

            $data['user_id'] = $staff->user_id ?? null;
            $data['teacher_id'] = null;

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
}