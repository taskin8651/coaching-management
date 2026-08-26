<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreEventPaymentRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('event_payment_collect'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'fee_account_id' => ['required', 'integer', 'exists:fee_accounts,id'],
            'paid_amount' => ['required', 'numeric', 'min:0.01'],

            'gst_applicable' => ['nullable', 'boolean'],
            'gst_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'gst_amount' => ['nullable', 'numeric', 'min:0'],

            'payment_mode' => ['required', 'in:cash,upi,bank_transfer,cheque,card,other'],
            'cheque_number' => ['nullable', 'string', 'max:100', 'required_if:payment_mode,cheque'],
            'cheque_date' => ['nullable', 'date', 'required_if:payment_mode,cheque'],
            'cheque_bank_name' => ['nullable', 'string', 'max:255'],
            'upi_txn_ref' => ['nullable', 'string', 'max:100', 'required_if:payment_mode,upi'],
            'neft_rtgs_imps_utr' => ['nullable', 'string', 'max:100', 'required_if:payment_mode,bank_transfer'],
            'neft_rtgs_imps_bank_name' => ['nullable', 'string', 'max:255'],
            'card_gateway_ref' => ['nullable', 'string', 'max:100', 'required_if:payment_mode,card'],
            'other_reference' => ['nullable', 'string', 'max:255', 'required_if:payment_mode,other'],

            'payment_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
