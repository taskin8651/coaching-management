<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class UpdateEventRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('event_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', Rule::unique('events', 'code')->ignore($this->event->id)],
            'event_type' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'venue' => ['nullable', 'string', 'max:255'],

            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'registration_start_date' => ['nullable', 'date'],
            'registration_end_date' => ['nullable', 'date', 'after_or_equal:registration_start_date'],

            'base_fee' => ['nullable', 'numeric', 'min:0'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'external_enrollment_allowed' => ['nullable', 'boolean'],

            'batch_ids' => ['nullable', 'array'],
            'batch_ids.*' => ['integer', 'exists:batches,id'],
        ];
    }
}
