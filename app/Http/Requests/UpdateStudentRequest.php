<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class UpdateStudentRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('student_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
                Rule::unique('students', 'user_id')->ignore($this->student->id),
            ],
            'account_name' => [
                'required',
                'string',
                'max:255',
            ],
            'account_email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->student->user_id),
            ],
            'account_password' => [
                Rule::requiredIf(! $this->student->user_id),
                'nullable',
                'string',
                'min:8',
            ],
            'guardian_user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
            'branch_id' => [
                'nullable',
                'integer',
                'exists:branches,id',
            ],
            'course_id' => [
                'nullable',
                'integer',
                'exists:courses,id',
            ],
            'batch_id' => [
                'nullable',
                'integer',
                'exists:batches,id',
            ],
            'student_code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('students', 'student_code')->ignore($this->student->id),
            ],
            'biometric_id' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('students', 'biometric_id')->ignore($this->student->id),
                Rule::unique('users', 'biometric_id')->ignore($this->student->user_id),
            ],
            'father_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'mother_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'guardian_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'guardian_phone' => [
                'nullable',
                'string',
                'max:20',
            ],
            'guardian_whatsapp' => [
                'nullable',
                'string',
                'max:20',
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
            'emergency_contact' => [
                'nullable',
                'string',
                'max:20',
            ],
            'date_of_birth' => [
                'nullable',
                'date',
            ],
            'gender' => [
                'nullable',
                'in:male,female,other',
            ],
            'address' => [
                'nullable',
                'string',
            ],
            'school_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'class_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'admission_date' => [
                'nullable',
                'date',
            ],
            'status' => [
                'required',
                'in:active,inactive,completed,dropped',
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
