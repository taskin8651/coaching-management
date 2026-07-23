@extends('layouts.admin')

@section('page-title', 'Add Extra Class')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.extra-classes.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Add Extra Class</h2>

        <p class="admin-page-subtitle">
            Add approved extra class details for teacher salary calculation
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.extra-classes.store') }}">
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>

                <div>
                    <p class="form-card-title">Class Information</p>
                    <p class="form-card-subtitle">Select teacher, batch and subject</p>
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
                                <option value="{{ $id }}" {{ old('teacher_id') == $id ? 'selected' : '' }}>
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
                    <label class="field-label" for="batch_id">Batch</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-users icon"></i>

                        <select name="batch_id"
                                id="batch_id"
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
                    <label class="field-label" for="subject_id">Subject</label>

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
                    @endif
                </div>

                <div class="form-info-box">
                    <p>
                        <i class="fas fa-info-circle"></i>
                        Extra class salary me tabhi count hogi jab approval status approved hoga.
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
                    <p class="form-card-title">Class Timing</p>
                    <p class="form-card-subtitle">Set extra class date and timing</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="class_date">
                        Class Date <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar-day icon"></i>

                        <input type="date"
                               name="class_date"
                               id="class_date"
                               required
                               value="{{ old('class_date', date('Y-m-d')) }}"
                               class="field-input {{ $errors->has('class_date') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('class_date'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('class_date') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="start_time">
                        Start Time <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-clock icon"></i>

                        <input type="time"
                               name="start_time"
                               id="start_time"
                               required
                               value="{{ old('start_time') }}"
                               class="field-input {{ $errors->has('start_time') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('start_time'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('start_time') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="end_time">
                        End Time <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-hourglass-end icon"></i>

                        <input type="time"
                               name="end_time"
                               id="end_time"
                               required
                               value="{{ old('end_time') }}"
                               class="field-input {{ $errors->has('end_time') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('end_time'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('end_time') }}
                        </p>
                    @else
                        <p class="field-hint">
                            <i class="fas fa-clock"></i>
                            Extra class duration salary minutes calculation me use hoga.
                        </p>
                    @endif
                </div>

            </div>
        </div>

        <div class="form-card" style="grid-column:1/-1;">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-rupee-sign"></i>
                </div>

                <div>
                    <p class="form-card-title">Approval & Salary</p>
                    <p class="form-card-subtitle">Set approval status, salary amount, reason and remarks</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="approval_status">Approval Status</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-user-check icon"></i>

                        <select name="approval_status"
                                id="approval_status"
                                class="field-input {{ $errors->has('approval_status') ? 'error' : '' }}">
                            <option value="pending" {{ old('approval_status', 'pending') === 'pending' ? 'selected' : '' }}>
                                Pending
                            </option>
                            <option value="approved" {{ old('approval_status') === 'approved' ? 'selected' : '' }}>
                                Approved
                            </option>
                            <option value="rejected" {{ old('approval_status') === 'rejected' ? 'selected' : '' }}>
                                Rejected
                            </option>
                        </select>
                    </div>

                    @if($errors->has('approval_status'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('approval_status') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="salary_amount">Salary Amount</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-rupee-sign icon"></i>

                        <input type="number"
                               step="0.01"
                               min="0"
                               name="salary_amount"
                               id="salary_amount"
                               value="{{ old('salary_amount', 0) }}"
                               placeholder="Enter salary amount"
                               class="field-input {{ $errors->has('salary_amount') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('salary_amount'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('salary_amount') }}
                        </p>
                    @else
                        <p class="field-hint">
                            <i class="fas fa-info-circle"></i>
                            Approved extra class amount salary calculation me add hoga.
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="reason">Reason</label>

                    <div class="input-icon-wrap textarea-wrap">
                        <i class="fas fa-align-left icon"></i>

                        <textarea name="reason"
                                  id="reason"
                                  rows="4"
                                  placeholder="Why this extra class was required?"
                                  class="field-input {{ $errors->has('reason') ? 'error' : '' }}">{{ old('reason') }}</textarea>
                    </div>

                    @if($errors->has('reason'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('reason') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="remarks">Remarks</label>

                    <div class="input-icon-wrap textarea-wrap">
                        <i class="fas fa-comment-dots icon"></i>

                        <textarea name="remarks"
                                  id="remarks"
                                  rows="4"
                                  placeholder="Enter remarks if any..."
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
                        <p class="stat-label">Class Time</p>
                        <p class="stat-value" id="previewTime" style="font-size:22px;">-</p>
                    </div>

                    <div class="stat-card">
                        <p class="stat-label">Approval</p>
                        <p class="stat-value" id="previewApproval" style="font-size:22px;">Pending</p>
                    </div>

                    <div class="stat-card">
                        <p class="stat-label">Salary Amount</p>
                        <p class="stat-value" id="previewAmount" style="font-size:22px;">₹0.00</p>
                    </div>

                    <div class="stat-card">
                        <p class="stat-label">Salary Count</p>
                        <p class="stat-value" id="previewCount" style="font-size:22px;">No</p>
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

        <a href="{{ route('admin.extra-classes.index') }}" class="btn-ghost">
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

function updateExtraClassPreview() {
    const start = document.getElementById('start_time');
    const end = document.getElementById('end_time');
    const approval = document.getElementById('approval_status');
    const amount = document.getElementById('salary_amount');

    const timeText = (start && start.value ? start.value : '-') + ' - ' + (end && end.value ? end.value : '-');
    const approvalValue = approval && approval.value ? approval.value : 'pending';
    const salaryAmount = parseFloat(amount && amount.value ? amount.value : 0);

    document.getElementById('previewTime').innerText = timeText;
    document.getElementById('previewApproval').innerText = formatText(approvalValue);
    document.getElementById('previewAmount').innerText = '₹' + salaryAmount.toFixed(2);
    document.getElementById('previewCount').innerText = approvalValue === 'approved' ? 'Yes' : 'No';
}

document.addEventListener('DOMContentLoaded', function () {
    ['start_time', 'end_time', 'approval_status', 'salary_amount'].forEach(function (id) {
        const el = document.getElementById(id);

        if (el) {
            el.addEventListener('input', updateExtraClassPreview);
            el.addEventListener('change', updateExtraClassPreview);
        }
    });

    const batchSelect = document.getElementById('batch_id');
    const subjectSelect = document.getElementById('subject_id');
    const subjectsByBatch = @json($subjectsByBatch);

    cascadeByParent(subjectSelect, batchSelect, subjectsByBatch, {
        placeholder: 'Optional',
        keepValue: @json(old('subject_id')),
    });

    updateExtraClassPreview();
});
</script>
@endsection