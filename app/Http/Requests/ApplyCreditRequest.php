<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class ApplyCreditRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('credit_apply'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'fee_installment_id' => ['required', 'integer', 'exists:fee_installments,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
