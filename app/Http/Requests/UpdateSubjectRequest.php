<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class UpdateSubjectRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('subject_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
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
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'subject_code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('subjects', 'subject_code')->ignore($this->subject->id),
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'status' => [
                'required',
                'in:active,inactive',
            ],
        ];
    }
}