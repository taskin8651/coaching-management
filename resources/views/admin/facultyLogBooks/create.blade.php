@extends('layouts.admin')

@section('page-title', 'Add Faculty Log')

@section('content')
<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.faculty-log-books.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">Add Faculty Log</h2>
        <p class="admin-page-subtitle">Timetable details are securely loaded from your assigned batch.</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.faculty-log-books.store') }}" id="facultyLogForm" enctype="multipart/form-data">
@csrf
<div class="admin-form-grid">
    <div class="form-card">
        <div class="form-card-header"><div class="form-card-icon"><i class="fas fa-calendar-check"></i></div><div><p class="form-card-title">Lecture Details</p><p class="form-card-subtitle">Select date and assigned batch</p></div></div>
        <div class="form-card-body">
            <div class="field-group"><label class="field-label" for="lecture_date">Lecture Date <span class="req">*</span></label><input type="date" name="lecture_date" id="lecture_date" value="{{ old('lecture_date', now('Asia/Kolkata')->toDateString()) }}" max="{{ now('Asia/Kolkata')->toDateString() }}" required class="field-input">@if($errors->has('lecture_date'))<p class="field-error">{{ $errors->first('lecture_date') }}</p>@endif</div>
            <div class="field-group"><label class="field-label" for="batch_id">Batch <span class="req">*</span></label><select name="batch_id" id="batch_id" required class="field-input"><option value="">Select Batch</option>@foreach($batches as $id => $name)<option value="{{ $id }}" {{ old('batch_id') == $id ? 'selected' : '' }}>{{ $name }}</option>@endforeach</select>@if($errors->has('batch_id'))<p class="field-error">{{ $errors->first('batch_id') }}</p>@endif</div>
            <div class="field-group"><label class="field-label" for="topic_taught">Topic Taught <span class="req">*</span></label><input type="text" name="topic_taught" id="topic_taught" value="{{ old('topic_taught') }}" required class="field-input" placeholder="Example: Algebra Chapter 1">@if($errors->has('topic_taught'))<p class="field-error">{{ $errors->first('topic_taught') }}</p>@endif</div>
            <div class="field-group"><label class="field-label" for="remarks">Home Work</label><textarea name="remarks" id="remarks" rows="4" class="field-input">{{ old('remarks') }}</textarea></div>
            <div class="field-group">
                <label class="field-label" for="attachments">Attachments</label>
                <input type="file" name="attachments[]" id="attachments" multiple class="field-input {{ $errors->has('attachments') ? 'error' : '' }}">
                @if($errors->has('attachments'))
                    <p class="field-error">{{ $errors->first('attachments') }}</p>
                @else
                    <p class="field-hint">Attachments become visible on the log only after admin/branch manager approval.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="form-card">
        <div class="form-card-header"><div class="form-card-icon"><i class="fas fa-lock"></i></div><div><p class="form-card-title">Timetable Details</p><p class="form-card-subtitle">Automatically loaded and locked</p></div></div>
        <div class="form-card-body">
            <div class="field-group"><label class="field-label">Branch</label><input id="timetable_branch" class="field-input" readonly value="-"></div>
            <div class="field-group"><label class="field-label">Subject</label><input id="timetable_subject" class="field-input" readonly value="-"></div>
            <div class="field-group"><label class="field-label">Scheduled Time</label><input id="timetable_schedule" class="field-input" readonly value="-"></div>
            <div class="form-info-box" id="timetable_message"><p><i class="fas fa-info-circle"></i> Select batch and lecture date to load timetable.</p></div>
        </div>
    </div>
</div>
<div class="form-actions"><button type="submit" class="btn-primary"><i class="fas fa-check"></i> {{ trans('global.save') }}</button><a href="{{ route('admin.faculty-log-books.index') }}" class="btn-ghost">{{ trans('global.cancel') }}</a></div>
</form>
@endsection

@section('scripts')
@parent
<script>
const timetableUrl = '{{ route('admin.faculty-log-books.timetable') }}';
const batch = document.getElementById('batch_id'), date = document.getElementById('lecture_date'), message = document.getElementById('timetable_message');
async function loadTimetable() {
    if (!batch.value || !date.value) return;
    message.innerHTML = '<p><i class="fas fa-spinner fa-spin"></i> Loading timetable...</p>';
    const url = `${timetableUrl}?batch_id=${encodeURIComponent(batch.value)}&lecture_date=${encodeURIComponent(date.value)}`;
    const response = await fetch(url, {headers: {'Accept': 'application/json'}}); const data = await response.json();
    if (!response.ok) { document.getElementById('timetable_branch').value='-'; document.getElementById('timetable_subject').value='-'; document.getElementById('timetable_schedule').value='-'; message.innerHTML=`<p class="field-error">${data.message || 'Timetable not found for selected batch and date.'}</p>`; return; }
    document.getElementById('timetable_branch').value = data.branch_name || '-'; document.getElementById('timetable_subject').value = data.subject_name || '-'; document.getElementById('timetable_schedule').value = `${data.scheduled_start_time} - ${data.scheduled_end_time}`; message.innerHTML='<p><i class="fas fa-check-circle"></i> Timetable loaded. These details will be verified again when saving.</p>';
}
batch.addEventListener('change', loadTimetable); date.addEventListener('change', loadTimetable); if (batch.value && date.value) loadTimetable();
</script>
@endsection
