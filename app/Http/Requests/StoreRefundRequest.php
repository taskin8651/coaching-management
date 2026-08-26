<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreRefundRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('refund_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'fee_payment_id' => ['nullable', 'integer', 'exists:fee_payments,id'],
            'fee_installment_id' => ['nullable', 'integer', 'exists:fee_installments,id'],
            'fee_account_id' => ['required', 'integer', 'exists:fee_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'mode' => ['required', 'in:cash,upi,bank_transfer,cheque,card,other'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'refund_date' => ['required', 'date'],
            'reason' => ['required', 'string'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
