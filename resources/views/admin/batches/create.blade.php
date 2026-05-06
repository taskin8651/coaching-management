@extends('layouts.admin')

@section('page-title', 'Add Batch')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.batches.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Add Batch</h2>

        <p class="admin-page-subtitle">
            Create a new batch under selected branch and course
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.batches.store') }}">
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-layer-group"></i>
                </div>

                <div>
                    <p class="form-card-title">Batch Information</p>
                    <p class="form-card-subtitle">Basic batch details</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="name">
                        Batch Name <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-layer-group icon"></i>

                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name') }}"
                               required
                               placeholder="Example: Morning Batch, NEET 2026"
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
                    <label class="field-label" for="batch_code">
                        Batch Code
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-barcode icon"></i>

                        <input type="text"
                               name="batch_code"
                               id="batch_code"
                               value="{{ old('batch_code') }}"
                               placeholder="BAT-001"
                               class="field-input {{ $errors->has('batch_code') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('batch_code'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('batch_code') }}
                        </p>
                    @else
                        <p class="field-hint">Batch code should be unique.</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="max_students">
                        Maximum Students
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-users icon"></i>

                        <input type="number"
                               name="max_students"
                               id="max_students"
                               min="1"
                               value="{{ old('max_students') }}"
                               placeholder="Example: 50"
                               class="field-input {{ $errors->has('max_students') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('max_students'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('max_students') }}
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
                    <p class="form-card-title">Branch & Course</p>
                    <p class="form-card-subtitle">Link batch with course</p>
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
                    @else
                        <p class="field-hint">Batch will be used for selected course.</p>
                    @endif
                </div>

                <div class="form-info-box">
                    <p>
                        <i class="fas fa-info-circle"></i>
                        Later students and teachers will be assigned to this batch.
                    </p>
                </div>

            </div>
        </div>

        <div class="form-card" style="grid-column: 1 / -1;">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>

                <div>
                    <p class="form-card-title">Schedule Details</p>
                    <p class="form-card-subtitle">Batch date and timing</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="field-group">
                            <label class="field-label" for="start_date">Start Date</label>

                            <div class="input-icon-wrap">
                                <i class="fas fa-calendar icon"></i>

                                <input type="date"
                                       name="start_date"
                                       id="start_date"
                                       value="{{ old('start_date') }}"
                                       class="field-input {{ $errors->has('start_date') ? 'error' : '' }}">
                            </div>

                            @if($errors->has('start_date'))
                                <p class="field-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $errors->first('start_date') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="field-group">
                            <label class="field-label" for="end_date">End Date</label>

                            <div class="input-icon-wrap">
                                <i class="fas fa-calendar-check icon"></i>

                                <input type="date"
                                       name="end_date"
                                       id="end_date"
                                       value="{{ old('end_date') }}"
                                       class="field-input {{ $errors->has('end_date') ? 'error' : '' }}">
                            </div>

                            @if($errors->has('end_date'))
                                <p class="field-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $errors->first('end_date') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="field-group">
                            <label class="field-label" for="start_time">Start Time</label>

                            <div class="input-icon-wrap">
                                <i class="fas fa-clock icon"></i>

                                <input type="time"
                                       name="start_time"
                                       id="start_time"
                                       value="{{ old('start_time') }}"
                                       class="field-input {{ $errors->has('start_time') ? 'error' : '' }}">
                            </div>

                            @if($errors->has('start_time'))
                                <p class="field-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $errors->first('start_time') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="field-group">
                            <label class="field-label" for="end_time">End Time</label>

                            <div class="input-icon-wrap">
                                <i class="fas fa-clock icon"></i>

                                <input type="time"
                                       name="end_time"
                                       id="end_time"
                                       value="{{ old('end_time') }}"
                                       class="field-input {{ $errors->has('end_time') ? 'error' : '' }}">
                            </div>

                            @if($errors->has('end_time'))
                                <p class="field-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $errors->first('end_time') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="description">
                        Description
                    </label>

                    <textarea name="description"
                              id="description"
                              rows="5"
                              placeholder="Enter batch description"
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

        <a href="{{ route('admin.batches.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</form>

@endsection