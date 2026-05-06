<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreBranchRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('branch_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'required',
                'max:255',
            ],
            'branch_code' => [
                'string',
                'nullable',
                'max:255',
                'unique:branches,branch_code',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],
            'address' => [
                'nullable',
                'string',
            ],
            'city' => [
                'nullable',
                'string',
                'max:255',
            ],
            'state' => [
                'nullable',
                'string',
                'max:255',
            ],
            'pincode' => [
                'nullable',
                'string',
                'max:20',
            ],
            'manager_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
            'status' => [
                'required',
                'in:active,inactive',
            ],
            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ];
    }
}