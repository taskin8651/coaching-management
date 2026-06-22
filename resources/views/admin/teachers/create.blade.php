@extends('layouts.admin')

@section('page-title', 'Add Teacher')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.teachers.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Add Teacher</h2>

        <p class="admin-page-subtitle">
            Create teacher profile and assign course, subject and batch
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.teachers.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>

                <div>
                    <p class="form-card-title">Teacher Information</p>
                    <p class="form-card-subtitle">Basic profile details</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="account_name">Account Name</label>
                    <div class="input-icon-wrap"><i class="fas fa-user icon"></i><input type="text" name="account_name" id="account_name" value="{{ old('account_name') }}" placeholder="Teacher name" class="field-input {{ $errors->has('account_name') ? 'error' : '' }}"></div>
                    @if($errors->has('account_name')) <p class="field-error">{{ $errors->first('account_name') }}</p> @endif
                </div>
                <div class="field-group">
                    <label class="field-label" for="account_email">Account Email</label>
                    <div class="input-icon-wrap"><i class="fas fa-envelope icon"></i><input type="email" name="account_email" id="account_email" value="{{ old('account_email') }}" placeholder="teacher@example.com" class="field-input {{ $errors->has('account_email') ? 'error' : '' }}"></div>
                    @if($errors->has('account_email')) <p class="field-error">{{ $errors->first('account_email') }}</p> @endif
                </div>
                <div class="field-group">
                    <label class="field-label" for="account_password">Account Password</label>
                    <div class="input-icon-wrap"><i class="fas fa-lock icon"></i><input type="password" name="account_password" id="account_password" placeholder="Minimum 8 characters" class="field-input {{ $errors->has('account_password') ? 'error' : '' }}"></div>
                    @if($errors->has('account_password')) <p class="field-error">{{ $errors->first('account_password') }}</p> @endif
                    <p class="field-hint">A User account is created automatically using these details.</p>
                </div>

                <div class="field-group">
                    <label class="field-label" for="branch_id">
                        Branch <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-school icon"></i>

                        <select name="branch_id"
                                id="branch_id"
                                required
                                class="field-input {{ $errors->has('branch_id') ? 'error' : '' }}">
                            @foreach($branches as $id => $branch)
                                <option value="{{ $id }}" {{ old('branch_id') == $id ? 'selected' : '' }}>
                                    {{ $branch }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('branch_id'))
                        <p class="field-error">{{ $errors->first('branch_id') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="biometric_id">Biometric Code</label>
                    <div class="input-icon-wrap"><i class="fas fa-fingerprint icon"></i><input type="text" name="biometric_id" id="biometric_id" value="{{ old('biometric_id') }}" placeholder="BIO-001" class="field-input {{ $errors->has('biometric_id') ? 'error' : '' }}"></div>
                    @if($errors->has('biometric_id')) <p class="field-error">{{ $errors->first('biometric_id') }}</p> @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="phone">Phone</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-phone icon"></i>

                        <input type="text"
                               name="phone"
                               id="phone"
                               value="{{ old('phone') }}"
                               placeholder="Enter phone number"
                               class="field-input {{ $errors->has('phone') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('phone'))
                        <p class="field-error">{{ $errors->first('phone') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="alternate_phone">Alternate Phone</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-phone-alt icon"></i>

                        <input type="text"
                               name="alternate_phone"
                               id="alternate_phone"
                               value="{{ old('alternate_phone') }}"
                               placeholder="Enter alternate phone"
                               class="field-input {{ $errors->has('alternate_phone') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('alternate_phone'))
                        <p class="field-error">{{ $errors->first('alternate_phone') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="qualification">Qualification</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-graduation-cap icon"></i>

                        <input type="text"
                               name="qualification"
                               id="qualification"
                               value="{{ old('qualification') }}"
                               placeholder="Example: M.Sc, B.Ed"
                               class="field-input {{ $errors->has('qualification') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('qualification'))
                        <p class="field-error">{{ $errors->first('qualification') }}</p>
                    @endif
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
                    <p class="form-card-subtitle">Experience, salary and joining details</p>
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
                               value="{{ old('experience') }}"
                               placeholder="Example: 5 Years"
                               class="field-input {{ $errors->has('experience') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('experience'))
                        <p class="field-error">{{ $errors->first('experience') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="subject_specialization">Subject Specialization</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-book-open icon"></i>

                        <input type="text"
                               name="subject_specialization"
                               id="subject_specialization"
                               value="{{ old('subject_specialization') }}"
                               placeholder="Example: Mathematics, Physics"
                               class="field-input {{ $errors->has('subject_specialization') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('subject_specialization'))
                        <p class="field-error">{{ $errors->first('subject_specialization') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="salary">Salary</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-rupee-sign icon"></i>

                        <input type="number"
                               step="0.01"
                               name="salary"
                               id="salary"
                               value="{{ old('salary') }}"
                               placeholder="Enter salary"
                               class="field-input {{ $errors->has('salary') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('salary'))
                        <p class="field-error">{{ $errors->first('salary') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="joining_date">Joining Date</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar icon"></i>

                        <input type="date"
                               name="joining_date"
                               id="joining_date"
                               value="{{ old('joining_date') }}"
                               class="field-input {{ $errors->has('joining_date') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('joining_date'))
                        <p class="field-error">{{ $errors->first('joining_date') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="status">Status</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-toggle-on icon"></i>

                        <select name="status" id="status" class="field-input">
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                        <p class="form-card-subtitle">Assign course, subject and batch row-wise</p>
                    </div>
                </div>

                <button type="button" class="btn-mini-primary" onclick="addAssignmentRow()">
                    <i class="fas fa-plus"></i>
                    Add Row
                </button>
            </div>

            <div class="form-card-body">

                <div id="assignmentRows">
                    <div class="assignment-row">
                        <div class="field-group mb-0">
                            <label class="field-label">Course</label>
                            <select name="course_ids[]" class="field-input">
                                @foreach($courses as $id => $course)
                                    <option value="{{ $id }}">{{ $course }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field-group mb-0">
                            <label class="field-label">Subject</label>
                            <select name="subject_ids[]" class="field-input">
                                @foreach($subjects as $id => $subject)
                                    <option value="{{ $id }}">{{ $subject }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field-group mb-0">
                            <label class="field-label">Batch</label>
                            <select name="batch_ids[]" class="field-input">
                                @foreach($batches as $id => $batch)
                                    <option value="{{ $id }}">{{ $batch }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="button" class="assignment-remove" onclick="removeAssignmentRow(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="form-info-box mt-3">
                    <p>
                        <i class="fas fa-info-circle"></i>
                        Example: Course = Class 10, Subject = Math, Batch = Morning.
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
                    <p class="form-card-subtitle">Upload teacher photo and documents</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="row">
                    <div class="col-md-6">
                        <div class="field-group">
                            <label class="field-label" for="photo">Photo</label>
                            <input type="file" name="photo" id="photo" class="field-input">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="field-group">
                            <label class="field-label" for="documents">Documents</label>
                            <input type="file" name="documents[]" id="documents" multiple class="field-input">
                        </div>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="address">Address</label>

                    <textarea name="address"
                              id="address"
                              rows="4"
                              placeholder="Enter address"
                              class="field-input">{{ old('address') }}</textarea>
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

<script>
const teacherCourseOptions = `{!! collect($courses)->map(fn($name, $id) => '<option value="'.$id.'">'.e($name).'</option>')->implode('') !!}`;
const teacherSubjectOptions = `{!! collect($subjects)->map(fn($name, $id) => '<option value="'.$id.'">'.e($name).'</option>')->implode('') !!}`;
const teacherBatchOptions = `{!! collect($batches)->map(fn($name, $id) => '<option value="'.$id.'">'.e($name).'</option>')->implode('') !!}`;

function addAssignmentRow() {
    const wrapper = document.getElementById('assignmentRows');

    const row = document.createElement('div');
    row.className = 'assignment-row';

    row.innerHTML = `
        <div class="field-group mb-0">
            <label class="field-label">Course</label>
            <select name="course_ids[]" class="field-input">
                ${teacherCourseOptions}
            </select>
        </div>

        <div class="field-group mb-0">
            <label class="field-label">Subject</label>
            <select name="subject_ids[]" class="field-input">
                ${teacherSubjectOptions}
            </select>
        </div>

        <div class="field-group mb-0">
            <label class="field-label">Batch</label>
            <select name="batch_ids[]" class="field-input">
                ${teacherBatchOptions}
            </select>
        </div>

        <button type="button" class="assignment-remove" onclick="removeAssignmentRow(this)">
            <i class="fas fa-times"></i>
        </button>
    `;

    wrapper.appendChild(row);
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
</script>
@endsection
