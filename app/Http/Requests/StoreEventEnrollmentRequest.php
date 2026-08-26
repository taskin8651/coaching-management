<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreEventEnrollmentRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('event_enroll'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'participant_type' => ['required', 'in:student,external'],
            'student_id' => ['required_if:participant_type,student', 'nullable', 'integer', 'exists:students,id'],

            'external_contact_id' => ['nullable', 'integer', 'exists:external_contacts,id'],

            'new_contact.name' => ['required_without:external_contact_id', 'nullable', 'string', 'max:255'],
            'new_contact.mobile' => ['required_without:external_contact_id', 'nullable', 'string', 'max:20'],
            'new_contact.gender' => ['nullable', 'string', 'max:20'],
            'new_contact.date_of_birth' => ['nullable', 'date'],
            'new_contact.standard' => ['nullable', 'string', 'max:100'],
            'new_contact.school_name' => ['nullable', 'string', 'max:255'],
            'new_contact.whatsapp_number' => ['nullable', 'string', 'max:20'],
            'new_contact.email' => ['nullable', 'email', 'max:255'],
            'new_contact.guardian_name' => ['nullable', 'string', 'max:255'],
            'new_contact.guardian_mobile' => ['nullable', 'string', 'max:20'],
            'new_contact.guardian_email' => ['nullable', 'email', 'max:255'],
            'new_contact.city' => ['nullable', 'string', 'max:100'],
            'new_contact.area' => ['nullable', 'string', 'max:100'],

            'remarks' => ['nullable', 'string'],
        ];
    }
}
