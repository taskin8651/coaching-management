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
            'fee_installment_id' => ['required_if:allocate_multiple,false,0,null', 'nullable', 'integer', 'exists:fee_installments,id'],
            'fee_account_id' => ['required', 'integer', 'exists:fee_accounts,id'],

            'allocate_multiple' => ['nullable', 'boolean'],
            'allocations' => ['nullable', 'array'],
            'allocations.*.fee_installment_id' => ['required_with:allocations', 'integer', 'exists:fee_installments,id'],
            'allocations.*.amount' => ['required_with:allocations', 'numeric', 'min:0.01'],
            'concession_id' => ['nullable', 'integer', 'exists:concessions,id'],
            'collected_by_id' => ['nullable', 'integer', 'exists:users,id'],

            'receipt_no' => ['nullable', 'string', 'max:255', 'unique:fee_payments,receipt_no'],

            'total_fee' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['required', 'numeric', 'min:0'],

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
            'payment_status' => ['nullable', 'in:paid,partial,due,cancelled'],

            'remarks' => ['nullable', 'string'],
        ];
    }
}
