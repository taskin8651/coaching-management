<?php

namespace App\Http\Requests;

use App\Models\User;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('user_edit');
    }

    public function rules()
    {
        return [
            'name' => [
                'string',
                'required',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore(request()->route('user')->id),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
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
                Rule::unique('users', 'biometric_id')->ignore(request()->route('user')->id),
            ],
            'email_verified_at' => [
                'nullable',
                'date',
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
            ],
            'remember_token' => [
                'nullable',
                'string',
                'max:100',
            ],
            'roles.*' => [
                'integer',
            ],
            'roles' => [
                'required',
                'array',
            ],
        ];
    }
}
