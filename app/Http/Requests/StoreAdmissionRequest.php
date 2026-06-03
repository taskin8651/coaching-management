<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreAdmissionRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('admission_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'student_id'          => ['required', 'integer', 'exists:students,id'],
            'branch_id'           => ['required', 'integer', 'exists:branches,id'],
            'course_id'           => ['nullable', 'integer', 'exists:courses,id'],
            'batch_id'            => ['nullable', 'integer', 'exists:batches,id'],
            'enquiry_id'          => ['nullable', 'integer', 'exists:enquiries,id'],

            'admission_date'      => ['nullable', 'date'],

            'previous_school'     => ['nullable', 'string', 'max:255'],
            'previous_class'      => ['nullable', 'string', 'max:255'],
            'qualification'       => ['nullable', 'string', 'max:255'],

            'father_name'         => ['nullable', 'string', 'max:255'],
            'mother_name'         => ['nullable', 'string', 'max:255'],
            'guardian_name'       => ['nullable', 'string', 'max:255'],
            'guardian_relation'   => ['nullable', 'string', 'max:255'],
            'guardian_phone'      => ['nullable', 'string', 'max:30'],
            'guardian_whatsapp'   => ['nullable', 'string', 'max:30'],
            'parent_email'        => ['nullable', 'email', 'max:255'],
            'emergency_contact'   => ['nullable', 'string', 'max:30'],

            'course_fee'          => ['nullable', 'numeric', 'min:0'],
            'admission_fee'       => ['nullable', 'numeric', 'min:0'],
            'discount'            => ['nullable', 'numeric', 'min:0'],

            'admission_source'    => ['nullable', 'string', 'max:255'],
            'status'              => ['required', 'in:pending,confirmed,rejected,cancelled,completed'],
            'remarks'             => ['nullable', 'string'],

            'documents'           => ['nullable', 'array'],
            'documents.*'         => ['file', 'max:5120'],
        ];
    }
}
