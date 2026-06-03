@extends('layouts.admin')
@section('page-title', 'Mark Student Attendance')
@section('content')
<div class="admin-page-head"><div><a href="{{ route('admin.student-attendances.index') }}" class="admin-back-link">{{ trans('global.back_to_list') }}</a><h2 class="admin-page-title">Mark Student Attendance</h2></div></div>
<form method="POST" action="{{ route('admin.student-attendances.store') }}">@csrf
<div class="admin-form-grid"><div class="form-card" style="grid-column:1/-1;"><div class="form-card-body">
<div class="field-group"><label class="field-label">Student</label><select name="student_id" class="field-input" required>@foreach($students as $id=>$name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select></div>
<div class="field-group"><label class="field-label">Batch</label><select name="batch_id" class="field-input" required>@foreach($batches as $id=>$name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select></div>
<div class="field-group"><label class="field-label">Subject</label><select name="subject_id" class="field-input">@foreach($subjects as $id=>$name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select></div>
<div class="field-group"><label class="field-label">Date</label><input type="date" name="attendance_date" class="field-input" value="{{ old('attendance_date', date('Y-m-d')) }}" required></div>
<div class="field-group"><label class="field-label">Scheduled Start</label><input type="time" name="scheduled_start_time" class="field-input"></div>
<div class="field-group"><label class="field-label">Scheduled End</label><input type="time" name="scheduled_end_time" class="field-input"></div>
<div class="field-group"><label class="field-label">Actual In</label><input type="time" name="actual_in_time" class="field-input"></div>
<div class="field-group"><label class="field-label">Actual Out</label><input type="time" name="actual_out_time" class="field-input"></div>
<div class="field-group"><label class="field-label">Status</label><select name="status" class="field-input"><option value="present">Present</option><option value="absent">Absent</option><option value="late">Late</option><option value="half_day">Half Day</option></select></div>
<input type="hidden" name="source" value="manual"><div class="field-group"><label class="field-label">Remarks</label><textarea name="remarks" class="field-input"></textarea></div>
</div></div></div><div class="form-actions"><button class="btn-primary"><i class="fas fa-check"></i> {{ trans('global.save') }}</button></div></form>
@endsection
