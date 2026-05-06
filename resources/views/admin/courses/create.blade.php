@extends('layouts.admin')

@section('page-title', 'Add Course')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.courses.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Add Course</h2>

        <p class="admin-page-subtitle">
            Create a new course under selected branch
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.courses.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-book"></i>
                </div>

                <div>
                    <p class="form-card-title">Course Information</p>
                    <p class="form-card-subtitle">Basic course details</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="name">
                        Course Name <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-book icon"></i>

                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name') }}"
                               required
                               placeholder="Example: Class 10th, NEET Foundation"
                               class="field-input {{ $errors->has('name') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('name'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('name') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="course_code">
                        Course Code
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-barcode icon"></i>

                        <input type="text"
                               name="course_code"
                               id="course_code"
                               value="{{ old('course_code') }}"
                               placeholder="CRS-001"
                               class="field-input {{ $errors->has('course_code') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('course_code'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('course_code') }}
                        </p>
                    @else
                        <p class="field-hint">Course code should be unique.</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="image">
                        Course Image
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-image icon"></i>

                        <input type="file"
                               name="image"
                               id="image"
                               accept="image/*"
                               class="field-input {{ $errors->has('image') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('image'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('image') }}
                        </p>
                    @else
                        <p class="field-hint">Allowed: JPG, JPEG, PNG, WEBP. Max: 2MB.</p>
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

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-school"></i>
                </div>

                <div>
                    <p class="form-card-title">Branch & Fee</p>
                    <p class="form-card-subtitle">Assign branch and course pricing</p>
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
                    @else
                        <p class="field-hint">Course will be visible under selected branch.</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="duration">
                        Duration
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-clock icon"></i>

                        <input type="text"
                               name="duration"
                               id="duration"
                               value="{{ old('duration') }}"
                               placeholder="Example: 6 Months / 1 Year"
                               class="field-input {{ $errors->has('duration') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('duration'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('duration') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="fee">
                        Course Fee
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-rupee-sign icon"></i>

                        <input type="number"
                               step="0.01"
                               min="0"
                               name="fee"
                               id="fee"
                               value="{{ old('fee', 0) }}"
                               placeholder="Enter course fee"
                               class="field-input {{ $errors->has('fee') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('fee'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('fee') }}
                        </p>
                    @endif
                </div>

                <div class="form-info-box">
                    <p>
                        <i class="fas fa-info-circle"></i>
                        Later this course will be linked with subjects, batches and students.
                    </p>
                </div>

            </div>
        </div>

        <div class="form-card" style="grid-column: 1 / -1;">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-align-left"></i>
                </div>

                <div>
                    <p class="form-card-title">Description</p>
                    <p class="form-card-subtitle">Course details and overview</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="field-group">
                    <label class="field-label" for="description">
                        Description
                    </label>

                    <textarea name="description"
                              id="description"
                              rows="5"
                              placeholder="Enter course description"
                              class="field-input {{ $errors->has('description') ? 'error' : '' }}">{{ old('description') }}</textarea>

                    @if($errors->has('description'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('description') }}
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

        <a href="{{ route('admin.courses.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</form>

@endsection