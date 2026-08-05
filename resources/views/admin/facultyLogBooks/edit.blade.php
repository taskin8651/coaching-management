@extends('layouts.admin')

@section('page-title', 'Edit Faculty Log')

@section('content')
<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.faculty-log-books.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">Edit Faculty Log</h2>
        <p class="admin-page-subtitle">Update your submitted lecture log before the day ends.</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.faculty-log-books.update', $facultyLogBook) }}" id="facultyLogForm">
    @csrf
    @method('PUT')

    <div class="admin-form-grid">
        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon"><i class="fas fa-calendar-check"></i></div>
                <div>
                    <p class="form-card-title">Lecture Details</p>
                    <p class="form-card-subtitle">Batch and date are locked — only notes can be updated</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="field-group">
                    <label class="field-label">Lecture Date <span class="req">*</span></label>
                    <input class="field-input"
                           type="text"
                           readonly
                           value="{{ \Carbon\Carbon::parse($facultyLogBook->lecture_date)->format('d M Y') }}">
                    <input type="hidden" name="lecture_date" id="lecture_date" value="{{ $facultyLogBook->lecture_date }}">
                    <p class="field-hint">Lecture date cannot be changed after submission.</p>
                </div>

                <div class="field-group">
                    <label class="field-label">Batch <span class="req">*</span></label>
                    <input class="field-input"
                           type="text"
                           readonly
                           value="{{ $facultyLogBook->batch->name ?? '-' }}">
                    <input type="hidden" name="batch_id" id="batch_id" value="{{ $facultyLogBook->batch_id }}">
                    <p class="field-hint">Batch cannot be changed after submission.</p>
                </div>

                <div class="field-group">
                    <label class="field-label">Topic Taught <span class="req">*</span></label>
                    <input class="field-input"
                           type="text"
                           name="topic_taught"
                           value="{{ old('topic_taught', $facultyLogBook->topic_taught) }}"
                           required>
                    @if($errors->has('topic_taught'))
                        <p class="field-error">{{ $errors->first('topic_taught') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label">Home Work</label>
                    <textarea class="field-input" name="remarks" rows="4">{{ old('remarks', $facultyLogBook->remarks) }}</textarea>
                </div>
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon"><i class="fas fa-lock"></i></div>
                <div>
                    <p class="form-card-title">Timetable Details</p>
                    <p class="form-card-subtitle">Automatically loaded and locked</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="field-group">
                    <label class="field-label">Branch</label>
                    <input id="timetable_branch" class="field-input" readonly value="{{ $facultyLogBook->branch->name ?? '-' }}">
                </div>

                <div class="field-group">
                    <label class="field-label">Subject</label>
                    <input id="timetable_subject" class="field-input" readonly value="{{ $facultyLogBook->subject->name ?? '-' }}">
                </div>

                <div class="field-group">
                    <label class="field-label">Scheduled Time</label>
                    <input id="timetable_schedule" class="field-input" readonly value="{{ $facultyLogBook->scheduled_start_time }} - {{ $facultyLogBook->scheduled_end_time }}">
                </div>

                <div class="form-info-box" id="timetable_message">
                    <p><i class="fas fa-info-circle"></i> Timetable is verified again when saving.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button class="btn-primary"><i class="fas fa-check"></i> {{ trans('global.save') }}</button>
        <a href="{{ route('admin.faculty-log-books.index') }}" class="btn-ghost">{{ trans('global.cancel') }}</a>
    </div>
</form>
@endsection

@section('scripts')
@parent
<script>
const timetableUrl = '{{ route('admin.faculty-log-books.timetable') }}';
const batch = document.getElementById('batch_id');
const date = document.getElementById('lecture_date');
const message = document.getElementById('timetable_message');

async function loadTimetable() {
    if (!batch.value || !date.value) return;

    const response = await fetch(`${timetableUrl}?batch_id=${encodeURIComponent(batch.value)}&lecture_date=${encodeURIComponent(date.value)}`, {
        headers: {'Accept': 'application/json'},
    });
    const data = await response.json();

    if (!response.ok) {
        message.innerHTML = `<p class="field-error">${data.message || 'Timetable not found for selected batch and date.'}</p>`;
        return;
    }

    document.getElementById('timetable_branch').value = data.branch_name || '-';
    document.getElementById('timetable_subject').value = data.subject_name || '-';
    document.getElementById('timetable_schedule').value = `${data.scheduled_start_time} - ${data.scheduled_end_time}`;
    message.innerHTML = '<p><i class="fas fa-check-circle"></i> Timetable loaded.</p>';
}

loadTimetable();
</script>
@endsection
