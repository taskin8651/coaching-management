<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreEnquiryFollowUpRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('enquiry_follow_up_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'follow_up_date' => ['nullable', 'date'],
            'follow_up_type' => ['nullable', 'string', 'max:255'],
            'response' => ['nullable', 'string'],
            'next_follow_up_date' => ['nullable', 'date'],
            'status' => ['required', 'in:new,follow_up,interested,not_interested,converted,rejected'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}