<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class UpdateFeeHeadRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('fee_master_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('fee_heads', 'code')->ignore($this->fee_head->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'gst_applicable' => ['nullable', 'boolean'],
            'default_gst_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
