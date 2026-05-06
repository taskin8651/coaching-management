@extends('layouts.admin')

@section('page-title', 'Add Student')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.students.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Add Student</h2>

        <p class="admin-page-subtitle">
            Create student profile and admission details
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.students.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>

                <div>
                    <p class="form-card-title">Student Information</p>
                    <p class="form-card-subtitle">Basic student details</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="user_id">
                        User Account
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-user icon"></i>

                        <select name="user_id"
                                id="user_id"
                                class="field-input {{ $errors->has('user_id') ? 'error' : '' }}">
                            @foreach($users as $id => $user)
                                <option value="{{ $id }}" {{ old('user_id') == $id ? 'selected' : '' }}>
                                    {{ $user }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('user_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('user_id') }}
                        </p>
                    @else
                        <p class="field-hint">Only users with Student role will appear here.</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="student_code">
                        Student Code
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-barcode icon"></i>

                        <input type="text"
                               name="student_code"
                               id="student_code"
                               value="{{ old('student_code') }}"
                               placeholder="STD-001"
                               class="field-input {{ $errors->has('student_code') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('student_code'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('student_code') }}
                        </p>
                    @else
                        <p class="field-hint">Student code should be unique.</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="phone">
                        Phone
                    </label>

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
                    <label class="field-label" for="gender">
                        Gender
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-venus-mars icon"></i>

                        <select name="gender"
                                id="gender"
                                class="field-input {{ $errors->has('gender') ? 'error' : '' }}">
                            <option value="">Please select</option>
                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    @if($errors->has('gender'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('gender') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="date_of_birth">
                        Date of Birth
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar icon"></i>

                        <input type="date"
                               name="date_of_birth"
                               id="date_of_birth"
                               value="{{ old('date_of_birth') }}"
                               class="field-input {{ $errors->has('date_of_birth') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('date_of_birth'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('date_of_birth') }}
                        </p>
                    @endif
                </div>

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-layer-group"></i>
                </div>

                <div>
                    <p class="form-card-title">Academic Mapping</p>
                    <p class="form-card-subtitle">Branch, course and batch details</p>
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
                        Course
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
                    <label class="field-label" for="batch_id">
                        Batch
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-users icon"></i>

                        <select name="batch_id"
                                id="batch_id"
                                class="field-input {{ $errors->has('batch_id') ? 'error' : '' }}">
                            @foreach($batches as $id => $batch)
                                <option value="{{ $id }}" {{ old('batch_id') == $id ? 'selected' : '' }}>
                                    {{ $batch }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('batch_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('batch_id') }}
                        </p>
                    @else
                        <p class="field-hint">Student will be assigned to selected batch.</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="school_name">
                        School / College Name
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-university icon"></i>

                        <input type="text"
                               name="school_name"
                               id="school_name"
                               value="{{ old('school_name') }}"
                               placeholder="Enter school or college name"
                               class="field-input {{ $errors->has('school_name') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('school_name'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('school_name') }}
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
                               placeholder="Example: 10th, 12th, Graduation"
                               class="field-input {{ $errors->has('class_name') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('class_name'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('class_name') }}
                        </p>
                    @endif
                </div>

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-users"></i>
                </div>

                <div>
                    <p class="form-card-title">Guardian Details</p>
                    <p class="form-card-subtitle">Parent/guardian information</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="father_name">
                        Father Name
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-user icon"></i>

                        <input type="text"
                               name="father_name"
                               id="father_name"
                               value="{{ old('father_name') }}"
                               placeholder="Enter father name"
                               class="field-input {{ $errors->has('father_name') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('father_name'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('father_name') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="mother_name">
                        Mother Name
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-user icon"></i>

                        <input type="text"
                               name="mother_name"
                               id="mother_name"
                               value="{{ old('mother_name') }}"
                               placeholder="Enter mother name"
                               class="field-input {{ $errors->has('mother_name') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('mother_name'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('mother_name') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="admission_date">
                        Admission Date
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar-check icon"></i>

                        <input type="date"
                               name="admission_date"
                               id="admission_date"
                               value="{{ old('admission_date') }}"
                               class="field-input {{ $errors->has('admission_date') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('admission_date'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('admission_date') }}
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
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="dropped" {{ old('status') == 'dropped' ? 'selected' : '' }}>Dropped</option>
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
                    <i class="fas fa-file-upload"></i>
                </div>

                <div>
                    <p class="form-card-title">Photo, Documents & Address</p>
                    <p class="form-card-subtitle">Upload student photo, documents and address</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="field-group">
                            <label class="field-label" for="photo">
                                Student Photo
                            </label>

                            <div class="input-icon-wrap">
                                <i class="fas fa-image icon"></i>

                                <input type="file"
                                       name="photo"
                                       id="photo"
                                       accept="image/*"
                                       class="field-input {{ $errors->has('photo') ? 'error' : '' }}">
                            </div>

                            @if($errors->has('photo'))
                                <p class="field-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $errors->first('photo') }}
                                </p>
                            @else
                                <p class="field-hint">Allowed: JPG, JPEG, PNG, WEBP. Max: 2MB.</p>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="field-group">
                            <label class="field-label" for="documents">
                                Documents
                            </label>

                            <div class="input-icon-wrap">
                                <i class="fas fa-file icon"></i>

                                <input type="file"
                                       name="documents[]"
                                       id="documents"
                                       multiple
                                       class="field-input {{ $errors->has('documents') ? 'error' : '' }}">
                            </div>

                            @if($errors->has('documents'))
                                <p class="field-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $errors->first('documents') }}
                                </p>
                            @else
                                <p class="field-hint">Allowed: image, PDF, DOC, DOCX. Max: 5MB each.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="address">
                        Address
                    </label>

                    <textarea name="address"
                              id="address"
                              rows="5"
                              placeholder="Enter student address"
                              class="field-input {{ $errors->has('address') ? 'error' : '' }}">{{ old('address') }}</textarea>

                    @if($errors->has('address'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('address') }}
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

        <a href="{{ route('admin.students.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</form>

@endsection