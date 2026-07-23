@extends('layouts.admin')

@section('page-title', 'Mark Student Attendance')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.student-attendances.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Mark Student Attendance</h2>

        <p class="admin-page-subtitle">
            Mark manual student attendance with batch, subject, timing and status
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.student-attendances.store') }}">
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-user-check"></i>
                </div>

                <div>
                    <p class="form-card-title">Student Information</p>
                    <p class="form-card-subtitle">Select student, batch and subject</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="student_id">
                        Student <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-user-graduate icon"></i>

                        <select name="student_id"
                                id="student_id"
                                required
                                class="field-input {{ $errors->has('student_id') ? 'error' : '' }}">
                            <option value="">Select Student</option>

                            @foreach($students as $id => $name)
                                <option value="{{ $id }}" {{ old('student_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('student_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('student_id') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="batch_id">
                        Batch <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-users icon"></i>

                        <select name="batch_id"
                                id="batch_id"
                                required
                                class="field-input {{ $errors->has('batch_id') ? 'error' : '' }}">
                            <option value="">Select Batch</option>

                            @foreach($batches as $id => $name)
                                <option value="{{ $id }}" {{ old('batch_id') == $id ? 'selected' : '' }}>
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
                                <option value="{{ $id }}" {{ old('subject_id') == $id ? 'selected' : '' }}>
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
                    @else
                        <p class="field-hint">
                            <i class="fas fa-info-circle"></i>
                            Subject optional hai, agar batch-wise attendance mark karna ho
                        </p>
                    @endif
                </div>

                <input type="hidden" name="source" value="manual">

                <div class="form-info-box">
                    <p>
                        <i class="fas fa-fingerprint"></i>
                        This attendance will be saved as manual source.
                    </p>
                </div>

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>

                <div>
                    <p class="form-card-title">Attendance Details</p>
                    <p class="form-card-subtitle">Date, scheduled time and actual timing</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="attendance_date">
                        Date <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar-day icon"></i>

                        <input type="date"
                               name="attendance_date"
                               id="attendance_date"
                               required
                               value="{{ old('attendance_date', date('Y-m-d')) }}"
                               class="field-input {{ $errors->has('attendance_date') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('attendance_date'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('attendance_date') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="scheduled_start_time">
                        Scheduled Start
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-clock icon"></i>

                        <input type="time"
                               name="scheduled_start_time"
                               id="scheduled_start_time"
                               value="{{ old('scheduled_start_time') }}"
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
                        Scheduled End
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-hourglass-end icon"></i>

                        <input type="time"
                               name="scheduled_end_time"
                               id="scheduled_end_time"
                               value="{{ old('scheduled_end_time') }}"
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
                    <label class="field-label" for="actual_in_time">
                        Actual In
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-sign-in-alt icon"></i>

                        <input type="time"
                               name="actual_in_time"
                               id="actual_in_time"
                               value="{{ old('actual_in_time') }}"
                               class="field-input {{ $errors->has('actual_in_time') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('actual_in_time'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('actual_in_time') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="actual_out_time">
                        Actual Out
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-sign-out-alt icon"></i>

                        <input type="time"
                               name="actual_out_time"
                               id="actual_out_time"
                               value="{{ old('actual_out_time') }}"
                               class="field-input {{ $errors->has('actual_out_time') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('actual_out_time'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('actual_out_time') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="status">
                        Status
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-toggle-on icon"></i>

                        <select name="status"
                                id="status"
                                class="field-input {{ $errors->has('status') ? 'error' : '' }}">
                            <option value="present" {{ old('status', 'present') == 'present' ? 'selected' : '' }}>Present</option>
                            <option value="absent" {{ old('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                            <option value="late" {{ old('status') == 'late' ? 'selected' : '' }}>Late</option>
                            <option value="half_day" {{ old('status') == 'half_day' ? 'selected' : '' }}>Half Day</option>
                        </select>
                    </div>

                    @if($errors->has('status'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('status') }}
                        </p>
                    @endif
                </div>

            </div>
        </div>

        <div class="form-card" style="grid-column:1/-1;">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-comment-dots"></i>
                </div>

                <div>
                    <p class="form-card-title">Remarks & Preview</p>
                    <p class="form-card-subtitle">Add optional remarks and review attendance status</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="remarks">
                        Remarks
                    </label>

                    <div class="input-icon-wrap textarea-wrap">
                        <i class="fas fa-align-left icon"></i>

                        <textarea name="remarks"
                                  id="remarks"
                                  rows="5"
                                  placeholder="Enter attendance remarks if any..."
                                  class="field-input {{ $errors->has('remarks') ? 'error' : '' }}">{{ old('remarks') }}</textarea>
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
                        <p class="stat-label">Source</p>
                        <p class="stat-value" style="font-size:22px;">Manual</p>
                    </div>

                    <div class="stat-card">
                        <p class="stat-label">Selected Status</p>
                        <p class="stat-value" id="previewStatus" style="font-size:22px;">Present</p>
                    </div>

                    <div class="stat-card">
                        <p class="stat-label">Scheduled Time</p>
                        <p class="stat-value" id="previewScheduled" style="font-size:22px;">-</p>
                    </div>

                    <div class="stat-card">
                        <p class="stat-label">Actual Time</p>
                        <p class="stat-value" id="previewActual" style="font-size:22px;">-</p>
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

        <a href="{{ route('admin.student-attendances.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</form>

@endsection

@section('scripts')
@parent
<script>
function updateAttendancePreview() {
    const status = document.getElementById('status');
    const scheduledStart = document.getElementById('scheduled_start_time');
    const scheduledEnd = document.getElementById('scheduled_end_time');
    const actualIn = document.getElementById('actual_in_time');
    const actualOut = document.getElementById('actual_out_time');

    const selectedStatus = status && status.value ? status.options[status.selectedIndex].text : 'Present';
    const scheduledText = (scheduledStart && scheduledStart.value ? scheduledStart.value : '-') + ' - ' + (scheduledEnd && scheduledEnd.value ? scheduledEnd.value : '-');
    const actualText = (actualIn && actualIn.value ? actualIn.value : '-') + ' - ' + (actualOut && actualOut.value ? actualOut.value : '-');

    document.getElementById('previewStatus').innerText = selectedStatus;
    document.getElementById('previewScheduled').innerText = scheduledText;
    document.getElementById('previewActual').innerText = actualText;
}

document.addEventListener('DOMContentLoaded', function () {
    [
        'status',
        'scheduled_start_time',
        'scheduled_end_time',
        'actual_in_time',
        'actual_out_time'
    ].forEach(function (id) {
        const el = document.getElementById(id);

        if (el) {
            el.addEventListener('input', updateAttendancePreview);
            el.addEventListener('change', updateAttendancePreview);
        }
    });

    const batchSelect = document.getElementById('batch_id');
    const subjectSelect = document.getElementById('subject_id');
    const subjectsByBatch = @json($subjectsByBatch);

    cascadeByParent(subjectSelect, batchSelect, subjectsByBatch, {
        placeholder: 'Optional',
        keepValue: @json(old('subject_id')),
    });

    updateAttendancePreview();
});
</script>
@endsection