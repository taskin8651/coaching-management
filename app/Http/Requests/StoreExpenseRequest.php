<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreExpenseRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('expense_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'branch_id'    => ['nullable', 'integer', 'exists:branches,id'],
            'paid_by_id'   => ['nullable', 'integer', 'exists:users,id'],

            'title'        => ['required', 'string', 'max:255'],
            'category'     => ['nullable', 'string', 'max:255'],
            'amount'       => ['required', 'numeric', 'min:0'],
            'expense_date' => ['nullable', 'date'],

            'payment_mode' => ['required', 'in:cash,upi,bank_transfer,cheque,card,other'],

            'vendor_name'  => ['nullable', 'string', 'max:255'],
            'bill_no'      => ['nullable', 'string', 'max:255'],

            'status'       => ['required', 'in:paid,pending,cancelled'],
            'remarks'      => ['nullable', 'string'],
        ];
    }
}