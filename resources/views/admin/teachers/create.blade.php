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
            Create teacher profile and attach branch, photo and documents
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
                    <p class="form-card-subtitle">Basic teacher details</p>
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
                        <p class="field-hint">Only users with Teacher role will appear here.</p>
                    @endif
                </div>

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

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-briefcase"></i>
                </div>

                <div>
                    <p class="form-card-title">Professional Details</p>
                    <p class="form-card-subtitle">Qualification, experience and salary</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="qualification">
                        Qualification
                    </label>

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
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('qualification') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="experience">
                        Experience
                    </label>

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
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('experience') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="subject_specialization">
                        Subject Specialization
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-book-open icon"></i>

                        <input type="text"
                               name="subject_specialization"
                               id="subject_specialization"
                               value="{{ old('subject_specialization') }}"
                               placeholder="Example: Physics, Maths"
                               class="field-input {{ $errors->has('subject_specialization') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('subject_specialization'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('subject_specialization') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="salary">
                        Salary
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-rupee-sign icon"></i>

                        <input type="number"
                               step="0.01"
                               min="0"
                               name="salary"
                               id="salary"
                               value="{{ old('salary', 0) }}"
                               placeholder="Enter salary"
                               class="field-input {{ $errors->has('salary') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('salary'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('salary') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="joining_date">
                        Joining Date
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar icon"></i>

                        <input type="date"
                               name="joining_date"
                               id="joining_date"
                               value="{{ old('joining_date') }}"
                               class="field-input {{ $errors->has('joining_date') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('joining_date'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('joining_date') }}
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
                    <p class="form-card-title">Photo & Documents</p>
                    <p class="form-card-subtitle">Upload teacher photo and certificates</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="field-group">
                            <label class="field-label" for="photo">
                                Teacher Photo
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
                              placeholder="Enter teacher address"
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

        <a href="{{ route('admin.teachers.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</form>

@endsection