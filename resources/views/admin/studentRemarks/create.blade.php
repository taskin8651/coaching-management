@extends('layouts.admin')

@section('page-title', 'Add Student Remark')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.student-remarks.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Add Student Remark</h2>

        <p class="admin-page-subtitle">
            Add positive, negative or neutral remark and control parent visibility
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.student-remarks.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>

                <div>
                    <p class="form-card-title">Student & Teacher</p>
                    <p class="form-card-subtitle">Select student and remark given by teacher</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="student_id">
                        Student <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-user-graduate icon"></i>

                        <select name="student_id"
                                id="student_id"
                                required
                                class="field-input {{ $errors->has('student_id') ? 'error' : '' }}">
                            <option value="">Select Student</option>

                            @foreach($students as $id => $name)
                                <option value="{{ $id }}" {{ old('student_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('student_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('student_id') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="teacher_id">
                        Teacher
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-chalkboard-teacher icon"></i>

                        <select name="teacher_id"
                                id="teacher_id"
                                class="field-input {{ $errors->has('teacher_id') ? 'error' : '' }}">
                            <option value="">Select Teacher</option>

                            @foreach($teachers as $id => $name)
                                <option value="{{ $id }}" {{ old('teacher_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('teacher_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('teacher_id') }}
                        </p>
                    @else
                        <p class="field-hint">
                            <i class="fas fa-info-circle"></i>
                            Optional teacher reference for this remark
                        </p>
                    @endif
                </div>

                <div class="form-info-box">
                    <p>
                        <i class="fas fa-lightbulb"></i>
                        This remark will be saved in the selected student profile.
                    </p>
                </div>

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-comment-dots"></i>
                </div>

                <div>
                    <p class="form-card-title">Remark Details</p>
                    <p class="form-card-subtitle">Type, date, title and remark message</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="remark_type">
                        Type
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-tags icon"></i>

                        <select name="remark_type"
                                id="remark_type"
                                class="field-input {{ $errors->has('remark_type') ? 'error' : '' }}">
                            <option value="positive" {{ old('remark_type', 'positive') == 'positive' ? 'selected' : '' }}>
                                Positive
                            </option>
                            <option value="negative" {{ old('remark_type') == 'negative' ? 'selected' : '' }}>
                                Negative
                            </option>
                            <option value="neutral" {{ old('remark_type') == 'neutral' ? 'selected' : '' }}>
                                Neutral
                            </option>
                        </select>
                    </div>

                    @if($errors->has('remark_type'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('remark_type') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="remark_date">
                        Date <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar-alt icon"></i>

                        <input type="date"
                               name="remark_date"
                               id="remark_date"
                               required
                               value="{{ old('remark_date', date('Y-m-d')) }}"
                               class="field-input {{ $errors->has('remark_date') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('remark_date'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('remark_date') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="title">
                        Title
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-heading icon"></i>

                        <input type="text"
                               name="title"
                               id="title"
                               value="{{ old('title') }}"
                               placeholder="Example: Good Performance"
                               class="field-input {{ $errors->has('title') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('title'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('title') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="remark">
                        Remark <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap textarea-wrap">
                        <i class="fas fa-align-left icon"></i>

                        <textarea name="remark"
                                  id="remark"
                                  rows="6"
                                  required
                                  placeholder="Write student remark here..."
                                  class="field-input {{ $errors->has('remark') ? 'error' : '' }}">{{ old('remark') }}</textarea>
                    </div>

                    @if($errors->has('remark'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('remark') }}
                        </p>
                    @else
                        <p class="field-hint">
                            <i class="fas fa-info-circle"></i>
                            Keep remark clear and parent-friendly
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="attachments">
                        Attachments
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-paperclip icon"></i>

                        <input type="file"
                               name="attachments[]"
                               id="attachments"
                               multiple
                               class="field-input {{ $errors->has('attachments') || $errors->has('attachments.*') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('attachments') || $errors->has('attachments.*'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('attachments') ?: $errors->first('attachments.*') }}
                        </p>
                    @else
                        <p class="field-hint">
                            <i class="fas fa-info-circle"></i>
                            Optional: photo, document proof etc. (max 10MB each, multiple files allowed)
                        </p>
                    @endif
                </div>

            </div>
        </div>

        <div class="form-card" style="grid-column:1/-1;">
            <div class="form-card-header between">
                <div class="form-card-head-left">
                    <div class="form-card-icon">
                        <i class="fas fa-eye"></i>
                    </div>

                    <div>
                        <p class="form-card-title">Parent Visibility</p>
                        <p class="form-card-subtitle">Control whether this remark is visible to parent portal</p>
                    </div>
                </div>
            </div>

            <div class="form-card-body">

                <label class="role-checkbox-item {{ old('visible_to_parent', 1) ? 'checked' : '' }}" style="max-width:360px;">
                    <input type="checkbox"
                           name="visible_to_parent"
                           value="1"
                           class="role-checkbox"
                           {{ old('visible_to_parent', 1) ? 'checked' : '' }}>

                    <div class="check-icon"></div>

                    <span class="checkbox-text">Visible to parent</span>
                </label>

                <div class="form-info-box" style="margin-top:16px;">
                    <p>
                        <i class="fas fa-info-circle"></i>
                        If enabled, guardian/parent can see this remark in student profile or parent portal.
                    </p>
                </div>

            </div>
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">
            <i class="fas fa-check"></i>
            {{ trans('global.save') }}
        </button>

        <a href="{{ route('admin.student-remarks.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</form>

@endsection