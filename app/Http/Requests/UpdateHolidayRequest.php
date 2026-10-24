<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

class UpdateHolidayRequest extends FormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('holiday_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'type' => ['required', 'in:mandatory,optional'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'in:festival,public_holiday,institute_break,sports_event,other'],
            'audiences' => ['required', 'array', 'min:1'],
            'audiences.*' => ['required', 'in:students,teachers,staff'],
            'target_mode' => ['required', 'in:all_branches,branch_wise,batch_wise,selected_students,selected_teachers,all_branch_teachers,single_branch_teachers,teacher_wise,all_branch_staff,single_branch_staff,selected_staff'],
            'target_branch_id' => ['nullable', 'required_if:target_mode,batch_wise,single_branch_teachers,single_branch_staff', 'integer', 'exists:branches,id'],
            'target_ids' => ['nullable', 'array'],
            'target_ids.*' => ['required', 'integer'],
        ];
    }
}
