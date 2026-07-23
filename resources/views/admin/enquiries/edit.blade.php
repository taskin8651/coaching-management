@extends('layouts.admin')

@section('page-title', 'Edit Enquiry')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.enquiries.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Edit Enquiry</h2>

        <p class="admin-page-subtitle">
            Update enquiry details and follow-up status
        </p>
    </div>

    <div class="identity-card">
        <div class="identity-avatar" style="background:#4F46E5;">
            {{ strtoupper(substr($enquiry->student_name, 0, 1)) }}
        </div>

        <div>
            <p class="identity-title">{{ $enquiry->student_name }}</p>
            <p class="identity-subtitle">ID #{{ $enquiry->id }}</p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.enquiries.update', $enquiry->id) }}">
    @method('PUT')
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>

                <div>
                    <p class="form-card-title">Student Details</p>
                    <p class="form-card-subtitle">Update enquiry information</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="student_name">
                        Student Name <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-user icon"></i>

                        <input type="text"
                               name="student_name"
                               id="student_name"
                               value="{{ old('student_name', $enquiry->student_name) }}"
                               required
                               class="field-input {{ $errors->has('student_name') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('student_name'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('student_name') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="phone">
                        Phone <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-phone icon"></i>

                        <input type="text"
                               name="phone"
                               id="phone"
                               value="{{ old('phone', $enquiry->phone) }}"
                               required
                               class="field-input {{ $errors->has('phone') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('phone'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('phone') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="alternate_phone">
                        Alternate Phone
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-phone-alt icon"></i>

                        <input type="text"
                               name="alternate_phone"
                               id="alternate_phone"
                               value="{{ old('alternate_phone', $enquiry->alternate_phone) }}"
                               class="field-input {{ $errors->has('alternate_phone') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('alternate_phone'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('alternate_phone') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="email">
                        Email
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-envelope icon"></i>

                        <input type="email"
                               name="email"
                               id="email"
                               value="{{ old('email', $enquiry->email) }}"
                               class="field-input {{ $errors->has('email') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('email'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('email') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="class_name">
                        Class
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-chalkboard icon"></i>

                        <input type="text"
                               name="class_name"
                               id="class_name"
                               value="{{ old('class_name', $enquiry->class_name) }}"
                               class="field-input {{ $errors->has('class_name') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('class_name'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('class_name') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="school_name">
                        School Name
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-university icon"></i>

                        <input type="text"
                               name="school_name"
                               id="school_name"
                               value="{{ old('school_name', $enquiry->school_name) }}"
                               class="field-input {{ $errors->has('school_name') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('school_name'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('school_name') }}
                        </p>
                    @endif
                </div>

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-headset"></i>
                </div>

                <div>
                    <p class="form-card-title">Enquiry Details</p>
                    <p class="form-card-subtitle">Update branch, course and status</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="branch_id">Branch</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-school icon"></i>

                        <select name="branch_id"
                                id="branch_id"
                                class="field-input {{ $errors->has('branch_id') ? 'error' : '' }}">
                            @foreach($branches as $id => $branch)
                                <option value="{{ $id }}" {{ old('branch_id', $enquiry->branch_id) == $id ? 'selected' : '' }}>
                                    {{ $branch }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('branch_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('branch_id') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="course_id">Interested Course</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-book icon"></i>

                        <select name="course_id"
                                id="course_id"
                                class="field-input {{ $errors->has('course_id') ? 'error' : '' }}">
                            @foreach($courses as $id => $course)
                                <option value="{{ $id }}" {{ old('course_id', $enquiry->course_id) == $id ? 'selected' : '' }}>
                                    {{ $course }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('course_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('course_id') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="assigned_to_id">Assigned To</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-user-tie icon"></i>

                        <select name="assigned_to_id"
                                id="assigned_to_id"
                                class="field-input {{ $errors->has('assigned_to_id') ? 'error' : '' }}">
                            @foreach($users as $id => $user)
                                <option value="{{ $id }}" {{ old('assigned_to_id', $enquiry->assigned_to_id) == $id ? 'selected' : '' }}>
                                    {{ $user }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('assigned_to_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('assigned_to_id') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="source">Source</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-bullhorn icon"></i>

                        <select name="source"
                                id="source"
                                class="field-input {{ $errors->has('source') ? 'error' : '' }}">
                            <option value="">Please select</option>
                            @foreach($sources as $key => $source)
                                <option value="{{ $key }}" {{ old('source', $enquiry->source) == $key ? 'selected' : '' }}>
                                    {{ $source }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('source'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('source') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="enquiry_date">Enquiry Date</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar icon"></i>

                        <input type="date"
                               name="enquiry_date"
                               id="enquiry_date"
                               value="{{ old('enquiry_date', optional($enquiry->enquiry_date)->format('Y-m-d')) }}"
                               class="field-input {{ $errors->has('enquiry_date') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('enquiry_date'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('enquiry_date') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="next_follow_up_date">Next Follow-up Date</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar-check icon"></i>

                        <input type="date"
                               name="next_follow_up_date"
                               id="next_follow_up_date"
                               value="{{ old('next_follow_up_date', optional($enquiry->next_follow_up_date)->format('Y-m-d')) }}"
                               class="field-input {{ $errors->has('next_follow_up_date') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('next_follow_up_date'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('next_follow_up_date') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="status">
                        Status <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-toggle-on icon"></i>

                        <select name="status"
                                id="status"
                                required
                                class="field-input {{ $errors->has('status') ? 'error' : '' }}">
                            <option value="new" {{ old('status', $enquiry->status) == 'new' ? 'selected' : '' }}>New</option>
                            <option value="follow_up" {{ old('status', $enquiry->status) == 'follow_up' ? 'selected' : '' }}>Follow Up</option>
                            <option value="interested" {{ old('status', $enquiry->status) == 'interested' ? 'selected' : '' }}>Interested</option>
                            <option value="not_interested" {{ old('status', $enquiry->status) == 'not_interested' ? 'selected' : '' }}>Not Interested</option>
                            <option value="converted" {{ old('status', $enquiry->status) == 'converted' ? 'selected' : '' }}>Converted</option>
                            <option value="rejected" {{ old('status', $enquiry->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
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

        <div class="form-card" style="grid-column: 1 / -1;">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-comment-dots"></i>
                </div>

                <div>
                    <p class="form-card-title">Remarks</p>
                    <p class="form-card-subtitle">Update enquiry notes</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="field-group">
                    <label class="field-label" for="remarks">Remarks</label>

                    <textarea name="remarks"
                              id="remarks"
                              rows="5"
                              class="field-input {{ $errors->has('remarks') ? 'error' : '' }}">{{ old('remarks', $enquiry->remarks) }}</textarea>

                    @if($errors->has('remarks'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('remarks') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">
            <i class="fas fa-check"></i>
            {{ trans('global.save') }}
        </button>

        <a href="{{ route('admin.enquiries.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</form>

@endsection

@section('scripts')
@parent
<script>
document.addEventListener('DOMContentLoaded', function () {
    const branchSelect = document.getElementById('branch_id');
    const courseSelect = document.getElementById('course_id');
    const coursesByBranch = @json($coursesByBranch);

    cascadeByParent(courseSelect, branchSelect, coursesByBranch, {
        placeholder: @json(trans('global.pleaseSelect')),
        keepValue: @json(old('course_id', $enquiry->course_id)),
    });
});
</script>
@endsection