<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreFeeHeadRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('fee_master_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:fee_heads,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'gst_applicable' => ['nullable', 'boolean'],
            'default_gst_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
