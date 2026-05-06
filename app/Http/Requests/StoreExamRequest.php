<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreExamRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('exam_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'branch_id'     => ['nullable', 'integer', 'exists:branches,id'],
            'course_id'     => ['nullable', 'integer', 'exists:courses,id'],
            'batch_id'      => ['nullable', 'integer', 'exists:batches,id'],
            'subject_id'    => ['nullable', 'integer', 'exists:subjects,id'],

            'title'         => ['required', 'string', 'max:255'],
            'exam_type'     => ['nullable', 'string', 'max:255'],

            'exam_date'     => ['nullable', 'date'],
            'start_time'    => ['nullable'],
            'end_time'      => ['nullable'],

            'total_marks'   => ['required', 'numeric', 'min:0'],
            'passing_marks' => ['required', 'numeric', 'min:0'],

            'status'        => ['required', 'in:scheduled,completed,cancelled'],
            'remarks'       => ['nullable', 'string'],
        ];
    }
}