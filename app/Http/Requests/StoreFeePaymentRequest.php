<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreFeePaymentRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('fee_payment_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'batch_id' => ['nullable', 'integer', 'exists:batches,id'],
            'fee_structure_id' => ['nullable', 'integer', 'exists:fee_structures,id'],
            'fee_installment_id' => ['nullable', 'integer', 'exists:fee_installments,id'],
            'collected_by_id' => ['nullable', 'integer', 'exists:users,id'],

            'receipt_no' => ['nullable', 'string', 'max:255', 'unique:fee_payments,receipt_no'],

            'total_fee' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['required', 'numeric', 'min:0'],

            'payment_mode' => ['required', 'in:cash,upi,bank_transfer,cheque,card,other'],
            'payment_date' => ['nullable', 'date'],
            'payment_status' => ['nullable', 'in:paid,partial,due,cancelled'],

            'remarks' => ['nullable', 'string'],
        ];
    }
}