<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class UpdateSalaryPaymentRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('salary_payment_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        $salaryPayment = $this->route('salary_payment');

        return [
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'teacher_id' => ['nullable', 'integer', 'exists:teachers,id'],
            'staff_id' => ['nullable', 'integer', 'exists:staff,id'],
            'paid_by_id' => ['nullable', 'integer', 'exists:users,id'],

            'employee_type' => ['required', 'in:teacher,staff,manager'],
            'salary_month' => ['required', 'string', 'max:20'],
            'slip_no' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('salary_payments', 'slip_no')->ignore($salaryPayment->id),
            ],

            'basic_salary' => ['required', 'numeric', 'min:0'],
            'bonus' => ['nullable', 'numeric', 'min:0'],
            'deduction' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['required', 'numeric', 'min:0'],

            'payment_mode' => ['required', 'in:cash,upi,bank_transfer,cheque,card,other'],
            'payment_date' => ['nullable', 'date'],
            'payment_status' => ['nullable', 'in:paid,partial,due,cancelled'],

            'remarks' => ['nullable', 'string'],
        ];
    }
}