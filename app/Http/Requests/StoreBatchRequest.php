<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreBatchRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('batch_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

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
            'subject_ids' => [
                'nullable',
                'array',
            ],
            'subject_ids.*' => [
                'integer',
                'exists:subjects,id',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'batch_code' => [
                'nullable',
                'string',
                'max:255',
                'unique:batches,batch_code',
            ],
            'start_time' => [
                'nullable',
                'date_format:H:i',
            ],
            'end_time' => [
                'nullable',
                'date_format:H:i',
            ],
            'max_students' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'status' => [
                'required',
                'in:active,inactive,completed',
            ],
        ];
    }
}
