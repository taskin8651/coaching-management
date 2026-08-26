<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class ApplyLateFeeRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('late_fee_apply'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'amount' => ['nullable', 'numeric', 'min:0.01'],
        ];
    }
}
