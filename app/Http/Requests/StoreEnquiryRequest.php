<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreEnquiryRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('enquiry_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'assigned_to_id' => ['nullable', 'integer', 'exists:users,id'],

            'student_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'alternate_phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],

            'class_name' => ['nullable', 'string', 'max:255'],
            'school_name' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],

            'enquiry_date' => ['nullable', 'date'],
            'next_follow_up_date' => ['nullable', 'date'],

            'status' => ['required', 'in:new,follow_up,interested,not_interested,converted,rejected'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}