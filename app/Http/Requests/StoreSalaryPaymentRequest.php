<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreSalaryPaymentRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('salary_payment_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'teacher_id' => ['nullable', 'integer', 'exists:teachers,id'],
            'staff_id' => ['nullable', 'integer', 'exists:staff,id'],
            'paid_by_id' => ['nullable', 'integer', 'exists:users,id'],

            'employee_type' => ['required', 'in:teacher,staff,manager'],
            'salary_month' => ['required', 'string', 'max:20'],
            'slip_no' => ['nullable', 'string', 'max:255', 'unique:salary_payments,slip_no'],

            'basic_salary' => ['required', 'numeric', 'min:0'],
            'bonus' => ['nullable', 'numeric', 'min:0'],
            'deduction' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['required', 'numeric', 'min:0', function ($attribute, $value, $fail) {
                $netSalary = max(((float) $this->input('basic_salary', 0) + (float) $this->input('bonus', 0)) - (float) $this->input('deduction', 0), 0);

                if ($netSalary > 0 && (float) $value > $netSalary + 0.01) {
                    $fail('Paid amount cannot exceed the net salary (₹' . number_format($netSalary, 2) . ').');
                }
            }],

            'payment_mode' => ['required', 'in:cash,upi,bank_transfer,cheque,card,other'],
            'payment_date' => ['nullable', 'date'],
            'payment_status' => ['nullable', 'in:paid,partial,due,cancelled'],

            'remarks' => ['nullable', 'string'],
        ];
    }
}