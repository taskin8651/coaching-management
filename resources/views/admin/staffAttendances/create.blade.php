@extends('layouts.admin')

@section('page-title', 'Mark Teacher & Staff Attendance')

@section('content')
<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.staff-attendances.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">Mark Teacher & Staff Attendance</h2>
        <p class="admin-page-subtitle">Choose a teacher with their assigned batch, or a staff member with their branch.</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.staff-attendances.store') }}">
@csrf
<div class="admin-form-grid">
    <div class="form-card">
        <div class="form-card-header"><div class="form-card-icon"><i class="fas fa-user-clock"></i></div><div><p class="form-card-title">Person & Work Location</p><p class="form-card-subtitle">Teacher is batch-wise; staff is branch-wise</p></div></div>
        <div class="form-card-body">
            <div class="field-group">
                <label class="field-label">Attendance For <span class="req">*</span></label>
                <div class="attendance-type-toggle">
                    <label><input type="radio" name="employee_type" value="teacher" {{ old('employee_type', 'teacher') === 'teacher' ? 'checked' : '' }}> Teacher</label>
                    <label><input type="radio" name="employee_type" value="staff" {{ old('employee_type') === 'staff' ? 'checked' : '' }}> Staff</label>
                </div>
                @if($errors->has('employee_type'))<p class="field-error">{{ $errors->first('employee_type') }}</p>@endif
            </div>
            <div id="teacherFields">
                <div class="field-group"><label class="field-label" for="teacher_id">Teacher <span class="req">*</span></label><select name="teacher_id" id="teacher_id" class="field-input"><option value="">Select Teacher</option>@foreach($teachers as $id => $name)<option value="{{ $id }}" {{ old('teacher_id') == $id ? 'selected' : '' }}>{{ $name }}</option>@endforeach</select>@if($errors->has('teacher_id'))<p class="field-error">{{ $errors->first('teacher_id') }}</p>@endif</div>
                <div class="field-group"><label class="field-label" for="batch_id">Assigned Batch <span class="req">*</span></label><select name="batch_id" id="batch_id" class="field-input"><option value="">Select Batch</option>@foreach($batches as $id => $name)<option value="{{ $id }}" {{ old('batch_id') == $id ? 'selected' : '' }}>{{ $name }}</option>@endforeach</select><p class="field-hint">Only the selected teacher's assigned batch can be saved.</p>@if($errors->has('batch_id'))<p class="field-error">{{ $errors->first('batch_id') }}</p>@endif</div>
            </div>
            <div id="staffFields">
                <div class="field-group"><label class="field-label" for="staff_id">Staff Member <span class="req">*</span></label><select name="staff_id" id="staff_id" class="field-input"><option value="">Select Staff</option>@foreach($staffMembers as $id => $name)<option value="{{ $id }}" {{ old('staff_id') == $id ? 'selected' : '' }}>{{ $name }}</option>@endforeach</select>@if($errors->has('staff_id'))<p class="field-error">{{ $errors->first('staff_id') }}</p>@endif</div>
                <div class="field-group"><label class="field-label" for="branch_id">Branch <span class="req">*</span></label><select name="branch_id" id="branch_id" class="field-input"><option value="">Select Branch</option>@foreach($branches as $id => $name)<option value="{{ $id }}" {{ old('branch_id') == $id ? 'selected' : '' }}>{{ $name }}</option>@endforeach</select><p class="field-hint">The staff member must belong to this branch.</p>@if($errors->has('branch_id'))<p class="field-error">{{ $errors->first('branch_id') }}</p>@endif</div>
            </div>
        </div>
    </div>
    <div class="form-card">
        <div class="form-card-header"><div class="form-card-icon"><i class="fas fa-calendar-check"></i></div><div><p class="form-card-title">Attendance Details</p><p class="form-card-subtitle">Date, timing and status</p></div></div>
        <div class="form-card-body">
            <div class="field-group"><label class="field-label" for="attendance_date">Date <span class="req">*</span></label><input class="field-input" type="date" name="attendance_date" id="attendance_date" value="{{ old('attendance_date', date('Y-m-d')) }}">@if($errors->has('attendance_date'))<p class="field-error">{{ $errors->first('attendance_date') }}</p>@endif</div>
            <div class="field-group"><label class="field-label" for="first_in_time">Check In</label><input class="field-input" type="time" name="first_in_time" id="first_in_time" value="{{ old('first_in_time') }}"></div>
            <div class="field-group"><label class="field-label" for="last_out_time">Check Out</label><input class="field-input" type="time" name="last_out_time" id="last_out_time" value="{{ old('last_out_time') }}"></div>
            <div class="field-group"><label class="field-label" for="status">Status <span class="req">*</span></label><select class="field-input" name="status" id="status"><option value="present" {{ old('status','present') === 'present' ? 'selected' : '' }}>Present</option><option value="absent" {{ old('status') === 'absent' ? 'selected' : '' }}>Absent</option><option value="late" {{ old('status') === 'late' ? 'selected' : '' }}>Late</option><option value="half_day" {{ old('status') === 'half_day' ? 'selected' : '' }}>Half Day</option></select></div>
            <div class="field-group"><label class="field-label" for="remarks">Remarks</label><textarea class="field-input" name="remarks" id="remarks" rows="3">{{ old('remarks') }}</textarea></div>
        </div>
    </div>
</div>
<div class="form-actions"><button type="submit" class="btn-primary"><i class="fas fa-check"></i> Save Attendance</button><a href="{{ route('admin.staff-attendances.index') }}" class="btn-ghost">{{ trans('global.cancel') }}</a></div>
</form>

<style>.attendance-type-toggle{display:flex;gap:12px}.attendance-type-toggle label{padding:10px 14px;border:1px solid #E2E8F0;border-radius:10px;cursor:pointer}</style>
@endsection

@section('scripts')
@parent
<script>
function toggleAttendanceFields() { const teacher = document.querySelector('input[name="employee_type"]:checked').value === 'teacher'; document.getElementById('teacherFields').style.display = teacher ? '' : 'none'; document.getElementById('staffFields').style.display = teacher ? 'none' : ''; }
document.querySelectorAll('input[name="employee_type"]').forEach(input => input.addEventListener('change', toggleAttendanceFields)); toggleAttendanceFields();
</script>
@endsection
