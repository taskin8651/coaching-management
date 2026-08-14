@extends('layouts.admin')

@section('page-title', 'Edit Teacher')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.teachers.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Edit Teacher</h2>

        <p class="admin-page-subtitle">
            Update teacher profile and teaching assignment
        </p>
    </div>

    <div class="identity-card">
        <div class="identity-avatar" style="background:#F59E0B;">
            {{ strtoupper(substr($teacher->user->name ?? 'T', 0, 1)) }}
        </div>

        <div>
            <p class="identity-title">{{ $teacher->user->name ?? 'Teacher' }}</p>
            <p class="identity-subtitle">ID #{{ $teacher->id }}</p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.teachers.update', $teacher->id) }}" enctype="multipart/form-data">
    @method('PUT')
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>

                <div>
                    <p class="form-card-title">Teacher Information</p>
                    <p class="form-card-subtitle">Update profile details</p>
                </div>
            </div>

            <div class="form-card-body">

                @include('admin.partials.profile-user-select', ['selectedUserId' => $teacher->user_id])

                <div class="field-group">
                    <label class="field-label" for="account_name">Teacher Name <span class="req">*</span></label>
                    <div class="input-icon-wrap">
                        <i class="fas fa-user icon"></i>
                        <input type="text" name="account_name" id="account_name" value="{{ old('account_name', $teacher->user->name ?? '') }}" placeholder="Teacher name" class="field-input {{ $errors->has('account_name') ? 'error' : '' }}">
                    </div>
                    @if($errors->has('account_name')) <p class="field-error">{{ $errors->first('account_name') }}</p> @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="account_email">Teacher Email <span class="req">*</span></label>
                    <div class="input-icon-wrap">
                        <i class="fas fa-envelope icon"></i>
                        <input type="email" name="account_email" id="account_email" value="{{ old('account_email', $teacher->user->email ?? '') }}" placeholder="teacher@example.com" class="field-input {{ $errors->has('account_email') ? 'error' : '' }}">
                    </div>
                    @if($errors->has('account_email')) <p class="field-error">{{ $errors->first('account_email') }}</p> @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="account_password">New Password</label>
                    <div class="input-icon-wrap">
                        <i class="fas fa-lock icon"></i>
                        <input type="password" name="account_password" id="account_password" placeholder="Leave blank to keep old password" class="field-input {{ $errors->has('account_password') ? 'error' : '' }}">
                    </div>
                    @if($errors->has('account_password')) <p class="field-error">{{ $errors->first('account_password') }}</p> @endif
                    <p class="field-hint">Password blank chhodne par old password same rahega.</p>
                </div>

                <div class="field-group">
                    <label class="field-label" for="branch_id">Branch <span class="req">*</span></label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-school icon"></i>

                        <select name="branch_id" id="branch_id" required class="field-input">
                            @foreach($branches as $id => $branch)
                                <option value="{{ $id }}" {{ old('branch_id', $teacher->branch_id) == $id ? 'selected' : '' }}>
                                    {{ $branch }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="biometric_id">Biometric Code</label>
                    <div class="input-icon-wrap"><i class="fas fa-fingerprint icon"></i><input type="text" name="biometric_id" id="biometric_id" value="{{ old('biometric_id', $teacher->biometric_id) }}" placeholder="BIO-001" class="field-input {{ $errors->has('biometric_id') ? 'error' : '' }}"></div>
                    @if($errors->has('biometric_id')) <p class="field-error">{{ $errors->first('biometric_id') }}</p> @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="device_id">Device ID</label>
                    <div class="input-icon-wrap"><i class="fas fa-mobile-alt icon"></i><input type="text" name="device_id" id="device_id" value="{{ old('device_id', $teacher->device_id) }}" placeholder="Device ID" class="field-input {{ $errors->has('device_id') ? 'error' : '' }}"></div>
                    @if($errors->has('device_id')) <p class="field-error">{{ $errors->first('device_id') }}</p> @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="phone">Phone</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-phone icon"></i>

                        <input type="text"
                               name="phone"
                               id="phone"
                               value="{{ old('phone', $teacher->phone) }}"
                               class="field-input">
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="alternate_phone">Alternate Phone</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-phone-alt icon"></i>

                        <input type="text"
                               name="alternate_phone"
                               id="alternate_phone"
                               value="{{ old('alternate_phone', $teacher->alternate_phone) }}"
                               class="field-input">
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="qualification">Qualification</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-graduation-cap icon"></i>

                        <input type="text"
                               name="qualification"
                               id="qualification"
                               value="{{ old('qualification', $teacher->qualification) }}"
                               class="field-input">
                    </div>
                </div>

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-briefcase"></i>
                </div>

                <div>
                    <p class="form-card-title">Job Details</p>
                    <p class="form-card-subtitle">Update experience, salary and status</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="experience">Experience</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-clock icon"></i>

                        <input type="text"
                               name="experience"
                               id="experience"
                               value="{{ old('experience', $teacher->experience) }}"
                               class="field-input">
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="subject_specialization">Subject Specialization</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-book-open icon"></i>

                        <input type="text"
                               name="subject_specialization"
                               id="subject_specialization"
                               value="{{ old('subject_specialization', $teacher->subject_specialization) }}"
                               class="field-input">
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="salary">Salary</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-rupee-sign icon"></i>

                        <input type="number"
                               step="0.01"
                               name="salary"
                               id="salary"
                               value="{{ old('salary', $teacher->salary) }}"
                               class="field-input">
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="joining_date">Joining Date</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar icon"></i>

                        <input type="date"
                               name="joining_date"
                               id="joining_date"
                               value="{{ old('joining_date', $teacher->joining_date ? \Carbon\Carbon::parse($teacher->joining_date)->format('Y-m-d') : '') }}"
                               class="field-input">
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="status">Status</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-toggle-on icon"></i>

                        <select name="status" id="status" class="field-input">
                            <option value="active" {{ old('status', $teacher->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $teacher->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

            </div>
        </div>

        <div class="form-card" style="grid-column: 1 / -1;">
            <div class="form-card-header between">
                <div class="form-card-head-left">
                    <div class="form-card-icon">
                        <i class="fas fa-chalkboard"></i>
                    </div>

                    <div>
                        <p class="form-card-title">Teaching Assignments</p>
                        <p class="form-card-subtitle">Update assigned course, subject and batch</p>
                    </div>
                </div>

                <button type="button" class="btn-mini-primary" onclick="addAssignmentRow()">
                    <i class="fas fa-plus"></i>
                    Add Row
                </button>
            </div>

            <div class="form-card-body">

                <div id="assignmentRows"></div>

                <div class="form-info-box mt-3">
                    <p>
                        <i class="fas fa-info-circle"></i>
                        Add multiple rows if teacher handles multiple batches or subjects.
                    </p>
                </div>

            </div>
        </div>

        <div class="form-card" style="grid-column: 1 / -1;">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-file-upload"></i>
                </div>

                <div>
                    <p class="form-card-title">Photo, Documents & Address</p>
                    <p class="form-card-subtitle">Update teacher photo and documents</p>
                </div>
            </div>

            <div class="form-card-body">

                @if($teacher->photo)
                    <div class="form-info-box mb-3">
                        <p>
                            <i class="fas fa-image"></i>
                            Current Photo:
                            <a href="{{ $teacher->photo }}" target="_blank">View Photo</a>
                        </p>
                    </div>
                @endif

                @if($teacher->documents && count($teacher->documents))
                    <div class="form-info-box mb-3">
                        <p class="mb-2">
                            <i class="fas fa-file"></i>
                            Uploaded Documents
                        </p>

                        @foreach($teacher->documents as $document)
                            <p>
                                <a href="{{ $document['url'] }}" target="_blank">
                                    {{ $document['name'] }}
                                </a>
                            </p>
                        @endforeach
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <div class="field-group">
                            <label class="field-label" for="photo">Replace Photo</label>
                            <input type="file" name="photo" id="photo" class="field-input">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="field-group">
                            <label class="field-label" for="documents">Add More Documents</label>
                            <input type="file" name="documents[]" id="documents" multiple class="field-input">
                        </div>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="address">Address</label>

                    <textarea name="address"
                              id="address"
                              rows="4"
                              class="field-input">{{ old('address', $teacher->address) }}</textarea>
                </div>

            </div>
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">
            <i class="fas fa-check"></i>
            {{ trans('global.save') }}
        </button>

        <a href="{{ route('admin.teachers.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</form>

<style>
.assignment-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 44px;
    gap: 14px;
    padding: 14px;
    border-radius: 18px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    margin-bottom: 12px;
    align-items: end;
}

.assignment-remove {
    width: 44px;
    height: 44px;
    border: none;
    border-radius: 14px;
    background: #FEE2E2;
    color: #991B1B;
    cursor: pointer;
}

.assignment-remove:hover {
    background: #EF4444;
    color: #fff;
}

@media (max-width: 991px) {
    .assignment-row {
        grid-template-columns: 1fr;
    }

    .assignment-remove {
        width: 100%;
    }
}
</style>

@php
    // Each old() entry is index-aligned across the three arrays (row 0's course/subject/batch, row 1's, ...).
    $initialAssignments = old('course_ids')
        ? collect(old('course_ids'))->map(fn ($courseId, $index) => [
            'course_id' => $courseId,
            'subject_id' => old('subject_ids')[$index] ?? null,
            'batch_id' => old('batch_ids')[$index] ?? null,
        ])->values()
        : $assignments->map(fn ($assignment) => [
            'course_id' => $assignment->course_id,
            'subject_id' => $assignment->subject_id,
            'batch_id' => $assignment->batch_id,
        ])->values();
@endphp

<script>
const coursesByBranch = @json($coursesByBranch);
const subjectsByBranch = @json($subjectsByBranch);
const batchesByBranch = @json($batchesByBranch);
const initialAssignments = @json($initialAssignments);

function filteredList(map, branchId) {
    return (branchId && map[branchId]) ? map[branchId] : [];
}

function fillSelect(select, items, selectedId) {
    if (!select) return;

    const currentValue = (selectedId !== undefined && selectedId !== null && selectedId !== '')
        ? String(selectedId)
        : (select.value || '');

    select.innerHTML = '';

    items.forEach(function (item) {
        const option = new Option(item.name, item.id);
        option.selected = String(item.id) === currentValue;
        select.appendChild(option);
    });
}

function populateRow(row, assignment) {
    const branchId = document.getElementById('branch_id').value;
    assignment = assignment || {};

    fillSelect(row.querySelector('.assignment-course'), filteredList(coursesByBranch, branchId), assignment.course_id);
    fillSelect(row.querySelector('.assignment-subject'), filteredList(subjectsByBranch, branchId), assignment.subject_id);
    fillSelect(row.querySelector('.assignment-batch'), filteredList(batchesByBranch, branchId), assignment.batch_id);
}

function buildRow(assignment) {
    const row = document.createElement('div');
    row.className = 'assignment-row';

    row.innerHTML = `
        <div class="field-group mb-0">
            <label class="field-label">Course</label>
            <select name="course_ids[]" class="field-input assignment-course"></select>
        </div>

        <div class="field-group mb-0">
            <label class="field-label">Subject</label>
            <select name="subject_ids[]" class="field-input assignment-subject"></select>
        </div>

        <div class="field-group mb-0">
            <label class="field-label">Batch</label>
            <select name="batch_ids[]" class="field-input assignment-batch"></select>
        </div>

        <button type="button" class="assignment-remove" onclick="removeAssignmentRow(this)">
            <i class="fas fa-times"></i>
        </button>
    `;

    populateRow(row, assignment);

    return row;
}

function addAssignmentRow() {
    document.getElementById('assignmentRows').appendChild(buildRow());
}

function removeAssignmentRow(button) {
    const wrapper = document.getElementById('assignmentRows');
    const rows = wrapper.querySelectorAll('.assignment-row');

    if (rows.length <= 1) {
        rows[0].querySelectorAll('select').forEach(select => select.value = '');
        return;
    }

    button.closest('.assignment-row').remove();
}

function refreshAllRows() {
    document.querySelectorAll('#assignmentRows .assignment-row').forEach(function (row) {
        populateRow(row, {});
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.getElementById('assignmentRows');
    const rows = initialAssignments.length ? initialAssignments : [{}];

    rows.forEach(function (assignment) {
        wrapper.appendChild(buildRow(assignment));
    });

    document.getElementById('branch_id').addEventListener('change', refreshAllRows);
});
</script>
@endsection
