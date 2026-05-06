<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class UpdateCourseRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('course_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

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
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'course_code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('courses', 'course_code')->ignore($this->course->id),
            ],
            'duration' => [
                'nullable',
                'string',
                'max:255',
            ],
            'fee' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'status' => [
                'required',
                'in:active,inactive',
            ],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ];
    }
}