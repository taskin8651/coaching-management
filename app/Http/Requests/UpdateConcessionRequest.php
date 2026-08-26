<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class UpdateConcessionRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('concession_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'type' => ['required', 'string', 'max:100'],
            'amount_type' => ['required', 'in:fixed,percentage'],
            'amount' => ['nullable', 'numeric', 'min:0', 'required_if:amount_type,fixed'],
            'percentage' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:amount_type,percentage'],
            'reason' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
