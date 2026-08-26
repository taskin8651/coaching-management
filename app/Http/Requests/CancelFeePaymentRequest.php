<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class CancelFeePaymentRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('fee_payment_cancel'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'cancel_reason' => ['required', 'string', 'min:5'],
        ];
    }
}
