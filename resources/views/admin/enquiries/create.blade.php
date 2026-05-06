@extends('layouts.admin')

@section('page-title', 'Add Enquiry')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.enquiries.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Add Enquiry</h2>

        <p class="admin-page-subtitle">
            Add new student enquiry and first follow-up details
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.enquiries.store') }}">
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>

                <div>
                    <p class="form-card-title">Student Details</p>
                    <p class="form-card-subtitle">Basic enquiry information</p>
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
                               value="{{ old('student_name') }}"
                               required
                               placeholder="Enter student name"
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
                               value="{{ old('phone') }}"
                               required
                               placeholder="Enter phone number"
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
                               value="{{ old('alternate_phone') }}"
                               placeholder="Enter alternate phone"
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
                               value="{{ old('email') }}"
                               placeholder="student@example.com"
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
                               value="{{ old('class_name') }}"
                               placeholder="Example: 10th, 12th"
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
                               value="{{ old('school_name') }}"
                               placeholder="Enter school name"
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
                    <p class="form-card-subtitle">Branch, course, source and status</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="branch_id">
                        Branch
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-school icon"></i>

                        <select name="branch_id"
                                id="branch_id"
                                class="field-input {{ $errors->has('branch_id') ? 'error' : '' }}">
                            @foreach($branches as $id => $branch)
                                <option value="{{ $id }}" {{ old('branch_id') == $id ? 'selected' : '' }}>
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
                    <label class="field-label" for="course_id">
                        Interested Course
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-book icon"></i>

                        <select name="course_id"
                                id="course_id"
                                class="field-input {{ $errors->has('course_id') ? 'error' : '' }}">
                            @foreach($courses as $id => $course)
                                <option value="{{ $id }}" {{ old('course_id') == $id ? 'selected' : '' }}>
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
                    <label class="field-label" for="assigned_to_id">
                        Assigned To
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-user-tie icon"></i>

                        <select name="assigned_to_id"
                                id="assigned_to_id"
                                class="field-input {{ $errors->has('assigned_to_id') ? 'error' : '' }}">
                            @foreach($users as $id => $user)
                                <option value="{{ $id }}" {{ old('assigned_to_id') == $id ? 'selected' : '' }}>
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
                    <label class="field-label" for="source">
                        Source
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-bullhorn icon"></i>

                        <select name="source"
                                id="source"
                                class="field-input {{ $errors->has('source') ? 'error' : '' }}">
                            <option value="">Please select</option>
                            @foreach($sources as $key => $source)
                                <option value="{{ $key }}" {{ old('source') == $key ? 'selected' : '' }}>
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
                    <label class="field-label" for="enquiry_date">
                        Enquiry Date
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar icon"></i>

                        <input type="date"
                               name="enquiry_date"
                               id="enquiry_date"
                               value="{{ old('enquiry_date', date('Y-m-d')) }}"
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
                    <label class="field-label" for="next_follow_up_date">
                        Next Follow-up Date
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar-check icon"></i>

                        <input type="date"
                               name="next_follow_up_date"
                               id="next_follow_up_date"
                               value="{{ old('next_follow_up_date') }}"
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
                            <option value="new" {{ old('status', 'new') == 'new' ? 'selected' : '' }}>New</option>
                            <option value="follow_up" {{ old('status') == 'follow_up' ? 'selected' : '' }}>Follow Up</option>
                            <option value="interested" {{ old('status') == 'interested' ? 'selected' : '' }}>Interested</option>
                            <option value="not_interested" {{ old('status') == 'not_interested' ? 'selected' : '' }}>Not Interested</option>
                            <option value="converted" {{ old('status') == 'converted' ? 'selected' : '' }}>Converted</option>
                            <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
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
                    <p class="form-card-subtitle">Initial enquiry notes</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="field-group">
                    <label class="field-label" for="remarks">
                        Remarks
                    </label>

                    <textarea name="remarks"
                              id="remarks"
                              rows="5"
                              placeholder="Enter enquiry remarks"
                              class="field-input {{ $errors->has('remarks') ? 'error' : '' }}">{{ old('remarks') }}</textarea>

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