<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreFeeAccountRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('fee_account_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:fee_accounts,code'],
            'type' => ['required', 'in:bank,cash,other'],
            'bank_name' => ['nullable', 'string', 'max:255', 'required_if:type,bank'],
            'account_number' => ['nullable', 'string', 'max:100', 'required_if:type,bank'],
            'ifsc_code' => ['nullable', 'string', 'max:20'],
            'upi_id' => ['nullable', 'string', 'max:100'],
            'qr_code' => ['nullable', 'image', 'max:2048'],
            'gst_applicable' => ['nullable', 'boolean'],
            'gst_number' => ['nullable', 'string', 'max:20', 'required_if:gst_applicable,1'],
            'receipt_address' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
