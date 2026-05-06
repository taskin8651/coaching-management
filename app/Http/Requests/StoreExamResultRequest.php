<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreExamResultRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('exam_result_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'results' => ['required', 'array'],

            'results.*.student_id'     => ['required', 'integer', 'exists:students,id'],
            'results.*.marks_obtained' => ['nullable', 'numeric', 'min:0'],
            'results.*.result_status'  => ['required', 'in:pass,fail,absent'],
            'results.*.remarks'        => ['nullable', 'string'],
        ];
    }
}