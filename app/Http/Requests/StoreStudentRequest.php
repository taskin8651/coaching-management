<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreStudentRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('student_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
                'unique:students,user_id',
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
                'unique:students,student_code',
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