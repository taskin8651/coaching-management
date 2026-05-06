@extends('layouts.admin')

@section('page-title', 'Edit Course')

@section('content')

@php
    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
@endphp

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.courses.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Edit Course</h2>

        <p class="admin-page-subtitle">
            Update course details, branch and fee
        </p>
    </div>

    <div class="identity-card">
        @if($course->image)
            <img src="{{ $course->image }}"
                 alt="{{ $course->name }}"
                 class="identity-avatar"
                 style="object-fit:cover;">
        @else
            <div class="identity-avatar" style="background: {{ $colors[$course->id % count($colors)] }};">
                {{ strtoupper(substr($course->name, 0, 1)) }}
            </div>
        @endif

        <div>
            <p class="identity-title">{{ $course->name }}</p>
            <p class="identity-subtitle">ID #{{ $course->id }}</p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.courses.update', $course->id) }}" enctype="multipart/form-data">
    @method('PUT')
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-book"></i>
                </div>

                <div>
                    <p class="form-card-title">Course Information</p>
                    <p class="form-card-subtitle">Update course details</p>
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
                               value="{{ old('name', $course->name) }}"
                               required
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
                               value="{{ old('course_code', $course->course_code) }}"
                               class="field-input {{ $errors->has('course_code') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('course_code'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('course_code') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="image">
                        Course Image
                    </label>

                    @if($course->image)
                        <div class="mb-2">
                            <img src="{{ $course->image }}"
                                 alt="{{ $course->name }}"
                                 style="width:90px; height:90px; object-fit:cover; border-radius:18px; border:1px solid #E2E8F0;">
                        </div>
                    @endif

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
                        <p class="field-hint">Upload new image only if you want to replace old image.</p>
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
                            <option value="active" {{ old('status', $course->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $course->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                    <p class="form-card-subtitle">Update branch and pricing</p>
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
                                <option value="{{ $id }}" {{ old('branch_id', $course->branch_id) == $id ? 'selected' : '' }}>
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
                    <label class="field-label" for="duration">
                        Duration
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-clock icon"></i>

                        <input type="text"
                               name="duration"
                               id="duration"
                               value="{{ old('duration', $course->duration) }}"
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
                               value="{{ old('fee', $course->fee) }}"
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
                    <p class="meta-label">Course Info</p>

                    <div class="meta-grid-2">
                        <div>
                            <p class="meta-small-label">Created</p>
                            <p class="meta-value-strong">
                                {{ optional($course->created_at)->format('d M Y') ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="meta-small-label">Status</p>

                            @if($course->status == 'active')
                                <p class="meta-value-strong meta-value-success">
                                    <i class="fas fa-check-circle"></i>
                                    Active
                                </p>
                            @else
                                <p class="meta-value-strong meta-value-warning">
                                    <i class="fas fa-clock"></i>
                                    Inactive
                                </p>
                            @endif
                        </div>
                    </div>
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
                    <p class="form-card-subtitle">Update course overview</p>
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
                              class="field-input {{ $errors->has('description') ? 'error' : '' }}">{{ old('description', $course->description) }}</textarea>

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