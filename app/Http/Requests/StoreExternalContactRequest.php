<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreExternalContactRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('external_contact_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'standard' => ['nullable', 'string', 'max:100'],
            'school_name' => ['nullable', 'string', 'max:255'],

            'mobile' => ['required', 'string', 'max:20'],
            'whatsapp_number' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],

            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_mobile' => ['nullable', 'string', 'max:20'],
            'guardian_email' => ['nullable', 'email', 'max:255'],

            'city' => ['nullable', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
