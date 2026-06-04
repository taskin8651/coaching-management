@extends('layouts.admin')

@section('page-title', 'Edit Faculty Log')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.faculty-log-books.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Edit Faculty Log</h2>

        <p class="admin-page-subtitle">
            Update teacher lecture log, actual timing, topic taught and approval status
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.faculty-log-books.update', $facultyLogBook->id) }}">
    @csrf
    @method('PUT')

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>

                <div>
                    <p class="form-card-title">Faculty & Academic Details</p>
                    <p class="form-card-subtitle">Update teacher, batch and subject</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="teacher_id">
                        Teacher <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-user-tie icon"></i>

                        <select name="teacher_id"
                                id="teacher_id"
                                required
                                class="field-input {{ $errors->has('teacher_id') ? 'error' : '' }}">
                            <option value="">Select Teacher</option>

                            @foreach($teachers as $id => $name)
                                <option value="{{ $id }}" {{ old('teacher_id', $facultyLogBook->teacher_id) == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('teacher_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('teacher_id') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="batch_id">
                        Batch
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-users icon"></i>

                        <select name="batch_id"
                                id="batch_id"
                                class="field-input {{ $errors->has('batch_id') ? 'error' : '' }}">
                            <option value="">Select Batch</option>

                            @foreach($batches as $id => $name)
                                <option value="{{ $id }}" {{ old('batch_id', $facultyLogBook->batch_id) == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('batch_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('batch_id') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="subject_id">
                        Subject
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-book-open icon"></i>

                        <select name="subject_id"
                                id="subject_id"
                                class="field-input {{ $errors->has('subject_id') ? 'error' : '' }}">
                            <option value="">Select Subject</option>

                            @foreach($subjects as $id => $name)
                                <option value="{{ $id }}" {{ old('subject_id', $facultyLogBook->subject_id) == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('subject_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('subject_id') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="topic_taught">
                        Topic Taught
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-book icon"></i>

                        <input type="text"
                               name="topic_taught"
                               id="topic_taught"
                               value="{{ old('topic_taught', $facultyLogBook->topic_taught) }}"
                               placeholder="Example: Algebra Chapter 1"
                               class="field-input {{ $errors->has('topic_taught') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('topic_taught'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('topic_taught') }}
                        </p>
                    @endif
                </div>

                <div class="form-info-box">
                    <p>
                        <i class="fas fa-info-circle"></i>
                        Teacher salary minutes actual class timing ke according calculate honge.
                    </p>
                </div>

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-clock"></i>
                </div>

                <div>
                    <p class="form-card-title">Lecture Timing</p>
                    <p class="form-card-subtitle">Update scheduled and actual teaching time</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="lecture_date">
                        Lecture Date <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar-day icon"></i>

                        <input type="date"
                               name="lecture_date"
                               id="lecture_date"
                               required
                               value="{{ old('lecture_date', $facultyLogBook->lecture_date ? \Carbon\Carbon::parse($facultyLogBook->lecture_date)->format('Y-m-d') : date('Y-m-d')) }}"
                               class="field-input {{ $errors->has('lecture_date') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('lecture_date'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('lecture_date') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="scheduled_start_time">
                        Scheduled Start <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-clock icon"></i>

                        <input type="time"
                               name="scheduled_start_time"
                               id="scheduled_start_time"
                               required
                               value="{{ old('scheduled_start_time', $facultyLogBook->scheduled_start_time) }}"
                               class="field-input {{ $errors->has('scheduled_start_time') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('scheduled_start_time'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('scheduled_start_time') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="scheduled_end_time">
                        Scheduled End <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-hourglass-end icon"></i>

                        <input type="time"
                               name="scheduled_end_time"
                               id="scheduled_end_time"
                               required
                               value="{{ old('scheduled_end_time', $facultyLogBook->scheduled_end_time) }}"
                               class="field-input {{ $errors->has('scheduled_end_time') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('scheduled_end_time'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('scheduled_end_time') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="actual_start_time">
                        Actual Start
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-sign-in-alt icon"></i>

                        <input type="time"
                               name="actual_start_time"
                               id="actual_start_time"
                               value="{{ old('actual_start_time', $facultyLogBook->actual_start_time) }}"
                               class="field-input {{ $errors->has('actual_start_time') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('actual_start_time'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('actual_start_time') }}
                        </p>
                    @else
                        <p class="field-hint">
                            <i class="fas fa-info-circle"></i>
                            Teacher late join kare to actual start time yahi save karein
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="actual_end_time">
                        Actual End
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-sign-out-alt icon"></i>

                        <input type="time"
                               name="actual_end_time"
                               id="actual_end_time"
                               value="{{ old('actual_end_time', $facultyLogBook->actual_end_time) }}"
                               class="field-input {{ $errors->has('actual_end_time') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('actual_end_time'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('actual_end_time') }}
                        </p>
                    @else
                        <p class="field-hint">
                            <i class="fas fa-clock"></i>
                            Batch end ke baad extra class tabhi count hoga jab admin/manager approve karega
                        </p>
                    @endif
                </div>

            </div>
        </div>

        <div class="form-card" style="grid-column:1/-1;">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-check-circle"></i>
                </div>

                <div>
                    <p class="form-card-title">Status & Remarks</p>
                    <p class="form-card-subtitle">Update log status, approval status and remarks</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="log_status">
                        Log Status
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-clipboard-check icon"></i>

                        <select name="log_status"
                                id="log_status"
                                class="field-input {{ $errors->has('log_status') ? 'error' : '' }}">
                            <option value="submitted" {{ old('log_status', $facultyLogBook->log_status) === 'submitted' ? 'selected' : '' }}>
                                Submitted
                            </option>
                            <option value="draft" {{ old('log_status', $facultyLogBook->log_status) === 'draft' ? 'selected' : '' }}>
                                Draft
                            </option>
                            <option value="missed" {{ old('log_status', $facultyLogBook->log_status) === 'missed' ? 'selected' : '' }}>
                                Missed
                            </option>
                            <option value="late_entry" {{ old('log_status', $facultyLogBook->log_status) === 'late_entry' ? 'selected' : '' }}>
                                Late Entry
                            </option>
                        </select>
                    </div>

                    @if($errors->has('log_status'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('log_status') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="approval_status">
                        Approval Status
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-user-check icon"></i>

                        <select name="approval_status"
                                id="approval_status"
                                class="field-input {{ $errors->has('approval_status') ? 'error' : '' }}">
                            <option value="pending" {{ old('approval_status', $facultyLogBook->approval_status) === 'pending' ? 'selected' : '' }}>
                                Pending
                            </option>
                            <option value="approved" {{ old('approval_status', $facultyLogBook->approval_status) === 'approved' ? 'selected' : '' }}>
                                Approved
                            </option>
                            <option value="rejected" {{ old('approval_status', $facultyLogBook->approval_status) === 'rejected' ? 'selected' : '' }}>
                                Rejected
                            </option>
                        </select>
                    </div>

                    @if($errors->has('approval_status'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('approval_status') }}
                        </p>
                    @else
                        <p class="field-hint">
                            <i class="fas fa-info-circle"></i>
                            Pending approval salary calculation me final approve ke baad count hoga
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="remarks">
                        Remarks
                    </label>

                    <div class="input-icon-wrap textarea-wrap">
                        <i class="fas fa-align-left icon"></i>

                        <textarea name="remarks"
                                  id="remarks"
                                  rows="5"
                                  placeholder="Enter remarks if any..."
                                  class="field-input {{ $errors->has('remarks') ? 'error' : '' }}">{{ old('remarks', $facultyLogBook->remarks) }}</textarea>
                    </div>

                    @if($errors->has('remarks'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('remarks') }}
                        </p>
                    @endif
                </div>

                <div class="stats-grid" style="margin-bottom:0;">
                    <div class="stat-card">
                        <p class="stat-label">Scheduled Time</p>
                        <p class="stat-value" id="previewScheduled" style="font-size:22px;">-</p>
                    </div>

                    <div class="stat-card">
                        <p class="stat-label">Actual Time</p>
                        <p class="stat-value" id="previewActual" style="font-size:22px;">-</p>
                    </div>

                    <div class="stat-card">
                        <p class="stat-label">Log Status</p>
                        <p class="stat-value" id="previewLogStatus" style="font-size:22px;">-</p>
                    </div>

                    <div class="stat-card">
                        <p class="stat-label">Approval</p>
                        <p class="stat-value" id="previewApproval" style="font-size:22px;">-</p>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">
            <i class="fas fa-check"></i>
            {{ trans('global.save') }}
        </button>

        <a href="{{ route('admin.faculty-log-books.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</form>

@endsection

@section('scripts')
@parent
<script>
function formatText(value) {
    if (!value) return '-';

    return value
        .replace(/_/g, ' ')
        .replace(/\b\w/g, function (char) {
            return char.toUpperCase();
        });
}

function updateFacultyLogPreview() {
    const scheduledStart = document.getElementById('scheduled_start_time');
    const scheduledEnd = document.getElementById('scheduled_end_time');
    const actualStart = document.getElementById('actual_start_time');
    const actualEnd = document.getElementById('actual_end_time');
    const logStatus = document.getElementById('log_status');
    const approvalStatus = document.getElementById('approval_status');

    const scheduledText = (scheduledStart && scheduledStart.value ? scheduledStart.value : '-') + ' - ' + (scheduledEnd && scheduledEnd.value ? scheduledEnd.value : '-');
    const actualText = (actualStart && actualStart.value ? actualStart.value : '-') + ' - ' + (actualEnd && actualEnd.value ? actualEnd.value : '-');

    document.getElementById('previewScheduled').innerText = scheduledText;
    document.getElementById('previewActual').innerText = actualText;
    document.getElementById('previewLogStatus').innerText = formatText(logStatus ? logStatus.value : '');
    document.getElementById('previewApproval').innerText = formatText(approvalStatus ? approvalStatus.value : '');
}

document.addEventListener('DOMContentLoaded', function () {
    [
        'scheduled_start_time',
        'scheduled_end_time',
        'actual_start_time',
        'actual_end_time',
        'log_status',
        'approval_status'
    ].forEach(function (id) {
        const el = document.getElementById(id);

        if (el) {
            el.addEventListener('input', updateFacultyLogPreview);
            el.addEventListener('change', updateFacultyLogPreview);
        }
    });

    updateFacultyLogPreview();
});
</script>
@endsection