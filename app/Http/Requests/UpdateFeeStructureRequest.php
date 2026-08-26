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
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'batch_id' => ['nullable', 'integer', 'exists:batches,id'],

            'title' => ['required', 'string', 'max:255'],
            'academic_year' => ['required', 'string', 'max:20'],
            'board' => ['nullable', 'string', 'max:100'],
            'standard' => ['nullable', 'string', 'max:100'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'installment_allocation_override' => ['nullable', 'boolean'],

            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.fee_head_id' => ['required', 'integer', 'exists:fee_heads,id'],
            'items.*.amount' => ['required', 'numeric', 'min:0'],
            'items.*.gst_applicable' => ['nullable', 'boolean'],
            'items.*.gst_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],

            'installments' => ['nullable', 'array'],
            'installments.*.title' => ['required_with:installments', 'string', 'max:255'],
            'installments.*.amount_type' => ['required_with:installments', 'in:fixed,percentage'],
            'installments.*.amount' => ['nullable', 'numeric', 'min:0'],
            'installments.*.percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'installments.*.due_date' => ['nullable', 'date'],
            'installments.*.fee_account_id' => ['required_with:installments', 'integer', 'exists:fee_accounts,id'],

            'installments.*.late_fee_enabled' => ['nullable', 'boolean'],
            'installments.*.late_fee_type' => ['nullable', 'in:fixed,percentage,per_day'],
            'installments.*.late_fee_amount' => ['nullable', 'numeric', 'min:0'],
            'installments.*.late_fee_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'installments.*.late_fee_grace_days' => ['nullable', 'integer', 'min:0'],
            'installments.*.late_fee_max_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
