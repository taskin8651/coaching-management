<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class UpdateStudyMaterialRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('study_material_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'branch_id'      => ['nullable', 'integer', 'exists:branches,id'],
            'course_id'      => ['nullable', 'integer', 'exists:courses,id'],
            'batch_id'       => ['nullable', 'integer', 'exists:batches,id'],
            'subject_id'     => ['nullable', 'integer', 'exists:subjects,id'],
            'uploaded_by_id' => ['nullable', 'integer', 'exists:users,id'],

            'title'          => ['required', 'string', 'max:255'],
            'material_type'  => ['nullable', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'external_link'  => ['nullable', 'url', 'max:500'],

            'status'         => ['required', 'in:active,inactive'],

            'files.*'        => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,webp,zip',
                'max:10240',
            ],
        ];
    }
}