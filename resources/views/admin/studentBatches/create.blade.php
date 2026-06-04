@extends('layouts.admin')

@section('page-title', 'Assign Student Batch')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.student-batches.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Assign Student Batch</h2>

        <p class="admin-page-subtitle">
            Assign student to batch, subject and active study period
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.student-batches.store') }}">
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>

                <div>
                    <p class="form-card-title">Student Assignment</p>
                    <p class="form-card-subtitle">Select student, batch and subject mapping</p>
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
                    <label class="field-label" for="batch_id">
                        Batch <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-users icon"></i>

                        <select name="batch_id"
                                id="batch_id"
                                required
                                class="field-input {{ $errors->has('batch_id') ? 'error' : '' }}">
                            <option value="">Select Batch</option>

                            @foreach($batches as $id => $name)
                                <option value="{{ $id }}" {{ old('batch_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
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
                    <label class="field-label" for="subject_id">
                        Subject
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-book-open icon"></i>

                        <select name="subject_id"
                                id="subject_id"
                                class="field-input {{ $errors->has('subject_id') ? 'error' : '' }}">
                            <option value="">Select Subject</option>

                            @foreach($subjects as $id => $name)
                                <option value="{{ $id }}" {{ old('subject_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('subject_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('subject_id') }}
                        </p>
                    @else
                        <p class="field-hint">
                            <i class="fas fa-info-circle"></i>
                            Subject is optional for full batch assignment
                        </p>
                    @endif
                </div>

                <div class="form-info-box">
                    <p>
                        <i class="fas fa-lightbulb"></i>
                        Ek student ko multiple batches me assign kar sakte ho, jaise 9-10 aur 10-11 dono batch.
                    </p>
                </div>

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>

                <div>
                    <p class="form-card-title">Batch Duration</p>
                    <p class="form-card-subtitle">Set start date, end date and assignment status</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="start_date">
                        Start Date
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar-day icon"></i>

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
                    @else
                        <p class="field-hint">
                            <i class="fas fa-info-circle"></i>
                            Leave blank if assignment starts immediately
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="end_date">
                        End Date
                    </label>

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
                    @else
                        <p class="field-hint">
                            <i class="fas fa-clock"></i>
                            Leave blank if batch is currently active
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="status">
                        Status
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-toggle-on icon"></i>

                        <select name="status"
                                id="status"
                                class="field-input {{ $errors->has('status') ? 'error' : '' }}">
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>
                                Active
                            </option>

                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>
                    </div>

                    @if($errors->has('status'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('status') }}
                        </p>
                    @endif
                </div>

                <div class="form-info-box">
                    <p>
                        <i class="fas fa-info-circle"></i>
                        Active status hone par student selected batch ke attendance, homework aur timetable me include hoga.
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

        <a href="{{ route('admin.student-batches.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</form>

@endsection