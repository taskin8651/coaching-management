<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class UpdateFeeStructureRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('fee_structure_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'branch_id'      => ['required', 'integer', 'exists:branches,id'],
            'course_id'      => ['required', 'integer', 'exists:courses,id'],
            'batch_id'       => ['nullable', 'integer', 'exists:batches,id'],

            'title'          => ['required', 'string', 'max:255'],

            'admission_fee'  => ['nullable', 'numeric', 'min:0'],
            'tuition_fee'    => ['nullable', 'numeric', 'min:0'],
            'exam_fee'       => ['nullable', 'numeric', 'min:0'],
            'material_fee'   => ['nullable', 'numeric', 'min:0'],
            'other_fee'      => ['nullable', 'numeric', 'min:0'],

            'description'    => ['nullable', 'string'],
            'status'         => ['required', 'in:active,inactive'],
        ];
    }
}