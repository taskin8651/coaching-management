<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreNoticeRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('notice_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'branch_id'       => ['nullable', 'integer', 'exists:branches,id'],
            'course_id'       => ['nullable', 'integer', 'exists:courses,id'],
            'batch_id'        => ['nullable', 'integer', 'exists:batches,id'],
            'created_by_id'   => ['nullable', 'integer', 'exists:users,id'],

            'title'           => ['required', 'string', 'max:255'],
            'notice_type'     => ['nullable', 'string', 'max:255'],
            'target_audience' => ['required', 'in:all,students,teachers,staff,managers,branch,course,batch'],

            'description'     => ['nullable', 'string'],
            'publish_date'    => ['nullable', 'date'],
            'expiry_date'     => ['nullable', 'date', 'after_or_equal:publish_date'],

            'status'          => ['required', 'in:draft,published,inactive'],

            'attachments.*'   => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx,jpg,jpeg,png,webp',
                'max:10240',
            ],
        ];
    }
}