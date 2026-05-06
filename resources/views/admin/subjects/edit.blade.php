@extends('layouts.admin')

@section('page-title', 'Edit Subject')

@section('content')

@php
    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
@endphp

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.subjects.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Edit Subject</h2>

        <p class="admin-page-subtitle">
            Update subject details and course mapping
        </p>
    </div>

    <div class="identity-card">
        <div class="identity-avatar" style="background: {{ $colors[$subject->id % count($colors)] }};">
            {{ strtoupper(substr($subject->name, 0, 1)) }}
        </div>

        <div>
            <p class="identity-title">{{ $subject->name }}</p>
            <p class="identity-subtitle">ID #{{ $subject->id }}</p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.subjects.update', $subject->id) }}">
    @method('PUT')
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-book-open"></i>
                </div>

                <div>
                    <p class="form-card-title">Subject Information</p>
                    <p class="form-card-subtitle">Update subject details</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="name">
                        Subject Name <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-book-open icon"></i>

                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name', $subject->name) }}"
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
                    <label class="field-label" for="subject_code">
                        Subject Code
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-barcode icon"></i>

                        <input type="text"
                               name="subject_code"
                               id="subject_code"
                               value="{{ old('subject_code', $subject->subject_code) }}"
                               class="field-input {{ $errors->has('subject_code') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('subject_code'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('subject_code') }}
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
                            <option value="active" {{ old('status', $subject->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $subject->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                    <i class="fas fa-layer-group"></i>
                </div>

                <div>
                    <p class="form-card-title">Branch & Course</p>
                    <p class="form-card-subtitle">Update course mapping</p>
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
                                <option value="{{ $id }}" {{ old('branch_id', $subject->branch_id) == $id ? 'selected' : '' }}>
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
                                <option value="{{ $id }}" {{ old('course_id', $subject->course_id) == $id ? 'selected' : '' }}>
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

                <div class="form-info-box">
                    <p class="meta-label">Subject Info</p>

                    <div class="meta-grid-2">
                        <div>
                            <p class="meta-small-label">Created</p>
                            <p class="meta-value-strong">
                                {{ optional($subject->created_at)->format('d M Y') ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="meta-small-label">Status</p>

                            @if($subject->status == 'active')
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
                    <p class="form-card-subtitle">Update subject overview</p>
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
                              class="field-input {{ $errors->has('description') ? 'error' : '' }}">{{ old('description', $subject->description) }}</textarea>

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

        <a href="{{ route('admin.subjects.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</form>

@endsection