<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreStaffRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('staff_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
                'unique:staff,user_id',
            ],
            'branch_id' => [
                'nullable',
                'integer',
                'exists:branches,id',
            ],
            'biometric_id' => [
                'nullable',
                'string',
                'max:255',
                'unique:staff,biometric_id',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],
            'alternate_phone' => [
                'nullable',
                'string',
                'max:20',
            ],
            'designation' => [
                'nullable',
                'string',
                'max:255',
            ],
            'department' => [
                'nullable',
                'string',
                'max:255',
            ],
            'address' => [
                'nullable',
                'string',
            ],
            'salary' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'salary_type' => [
                'nullable',
                'in:monthly',
            ],
            'joining_date' => [
                'nullable',
                'date',
            ],
            'status' => [
                'required',
                'in:active,inactive',
            ],
            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'documents.*' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf,doc,docx',
                'max:5120',
            ],
        ];
    }
}
