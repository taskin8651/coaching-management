@extends('layouts.admin')

@section('page-title', 'Edit Student')

@section('content')

@php
    $name = $student->user->name ?? 'Student';
    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
@endphp

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.students.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Edit Student</h2>

        <p class="admin-page-subtitle">
            Update student profile and admission details
        </p>
    </div>

    <div class="identity-card">
        @if($student->photo)
            <img src="{{ $student->photo }}"
                 alt="{{ $name }}"
                 class="identity-avatar"
                 style="object-fit:cover;">
        @else
            <div class="identity-avatar" style="background: {{ $colors[$student->id % count($colors)] }};">
                {{ strtoupper(substr($name, 0, 1)) }}
            </div>
        @endif

        <div>
            <p class="identity-title">{{ $name }}</p>
            <p class="identity-subtitle">ID #{{ $student->id }}</p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.students.update', $student->id) }}" enctype="multipart/form-data">
    @method('PUT')
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>

                <div>
                    <p class="form-card-title">Student Information</p>
                    <p class="form-card-subtitle">Update basic student details</p>
                </div>
            </div>

            <div class="form-card-body">

                @include('admin.partials.profile-user-select', ['selectedUserId' => $student->user_id])

                <div class="field-group">
                    <label class="field-label" for="account_name">Account Name <span class="req">*</span></label>
                    <div class="input-icon-wrap">
                        <i class="fas fa-user icon"></i>
                        <input type="text"
                               name="account_name"
                               id="account_name"
                               value="{{ old('account_name', $student->user->name ?? '') }}"
                               placeholder="Student name"
                               class="field-input {{ $errors->has('account_name') ? 'error' : '' }}">
                    </div>
                    @if($errors->has('account_name')) <p class="field-error">{{ $errors->first('account_name') }}</p> @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="account_email">Account Email <span class="req">*</span></label>
                    <div class="input-icon-wrap">
                        <i class="fas fa-envelope icon"></i>
                        <input type="email"
                               name="account_email"
                               id="account_email"
                               value="{{ old('account_email', $student->user->email ?? '') }}"
                               placeholder="student@example.com"
                               class="field-input {{ $errors->has('account_email') ? 'error' : '' }}">
                    </div>
                    @if($errors->has('account_email')) <p class="field-error">{{ $errors->first('account_email') }}</p> @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="account_password">New Password</label>
                    <div class="input-icon-wrap">
                        <i class="fas fa-lock icon"></i>
                        <input type="password"
                               name="account_password"
                               id="account_password"
                               placeholder="Leave blank to keep old password"
                               class="field-input {{ $errors->has('account_password') ? 'error' : '' }}">
                    </div>
                    @if($errors->has('account_password')) <p class="field-error">{{ $errors->first('account_password') }}</p> @endif
                    <p class="field-hint">Password blank chhodne par old password same rahega.</p>
                </div>

                <div class="field-group">
                    <label class="field-label" for="guardian_user_id">
                        Parent Account
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-user-friends icon"></i>

                        <select name="guardian_user_id"
                                id="guardian_user_id"
                                class="field-input {{ $errors->has('guardian_user_id') ? 'error' : '' }}">
                            @foreach($guardians as $id => $guardian)
                                <option value="{{ $id }}" {{ old('guardian_user_id', $student->guardian_user_id) == $id ? 'selected' : '' }}>
                                    {{ $guardian }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('guardian_user_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('guardian_user_id') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="biometric_id">Biometric Code</label>
                    <div class="input-icon-wrap"><i class="fas fa-fingerprint icon"></i><input type="text" name="biometric_id" id="biometric_id" value="{{ old('biometric_id', $student->biometric_id) }}" placeholder="BIO-001" class="field-input {{ $errors->has('biometric_id') ? 'error' : '' }}"></div>
                    @if($errors->has('biometric_id')) <p class="field-error">{{ $errors->first('biometric_id') }}</p> @endif
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
                               value="{{ old('phone', $student->phone) }}"
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
                               value="{{ old('alternate_phone', $student->alternate_phone) }}"
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
                            <option value="male" {{ old('gender', $student->gender) == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $student->gender) == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender', $student->gender) == 'other' ? 'selected' : '' }}>Other</option>
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
                               value="{{ old('date_of_birth', optional($student->date_of_birth)->format('Y-m-d')) }}"
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
                    <p class="form-card-subtitle">Update branch, course and batch</p>
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
                                <option value="{{ $id }}" {{ old('branch_id', $student->branch_id) == $id ? 'selected' : '' }}>
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
                                <option value="{{ $id }}" {{ old('course_id', $student->course_id) == $id ? 'selected' : '' }}>
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
                                <option value="{{ $id }}" {{ old('batch_id', $student->batch_id) == $id ? 'selected' : '' }}>
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
                               value="{{ old('school_name', $student->school_name) }}"
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
                               value="{{ old('class_name', $student->class_name) }}"
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
                    <p class="form-card-subtitle">Update parent/guardian information</p>
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
                               value="{{ old('father_name', $student->father_name) }}"
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
                               value="{{ old('mother_name', $student->mother_name) }}"
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
                               value="{{ old('admission_date', optional($student->admission_date)->format('Y-m-d')) }}"
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
                            <option value="active" {{ old('status', $student->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $student->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="completed" {{ old('status', $student->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="dropped" {{ old('status', $student->status) == 'dropped' ? 'selected' : '' }}>Dropped</option>
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
                    <p class="form-card-subtitle">Update student photo, documents and address</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="field-group">
                            <label class="field-label" for="photo">
                                Student Photo
                            </label>

                            @if($student->photo)
                                <div class="mb-2">
                                    <img src="{{ $student->photo }}"
                                         alt="{{ $name }}"
                                         style="width:90px; height:90px; object-fit:cover; border-radius:18px; border:1px solid #E2E8F0;">
                                </div>
                            @endif

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
                                <p class="field-hint">Upload new photo only if you want to replace old photo.</p>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="field-group">
                            <label class="field-label" for="documents">
                                Add More Documents
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
                                <p class="field-hint">New documents will be added with existing documents.</p>
                            @endif
                        </div>

                        @if($student->documents && count($student->documents))
                            <div class="form-info-box">
                                <p class="meta-label">Uploaded Documents</p>

                                @foreach($student->documents as $document)
                                    <p style="margin:6px 0;">
                                        <i class="fas fa-file"></i>
                                        <a href="{{ $document['url'] }}" target="_blank">
                                            {{ $document['name'] }}
                                        </a>
                                    </p>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="address">
                        Address
                    </label>

                    <textarea name="address"
                              id="address"
                              rows="5"
                              class="field-input {{ $errors->has('address') ? 'error' : '' }}">{{ old('address', $student->address) }}</textarea>

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
