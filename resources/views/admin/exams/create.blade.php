@extends('layouts.admin')

@section('page-title', 'Add Exam')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.exams.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Add Exam / Test</h2>

        <p class="admin-page-subtitle">
            Create exam for selected branch, course, batch and subject
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.exams.store') }}">
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>

                <div>
                    <p class="form-card-title">Exam Details</p>
                    <p class="form-card-subtitle">Basic exam/test information</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="title">
                        Exam Title <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-heading icon"></i>

                        <input type="text"
                               name="title"
                               id="title"
                               value="{{ old('title') }}"
                               required
                               placeholder="Example: Monthly Test - Physics"
                               class="field-input {{ $errors->has('title') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('title'))
                        <p class="field-error">{{ $errors->first('title') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="exam_type">Exam Type</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-list icon"></i>

                        <select name="exam_type"
                                id="exam_type"
                                class="field-input {{ $errors->has('exam_type') ? 'error' : '' }}">
                            <option value="">Please select</option>
                            @foreach($examTypes as $key => $type)
                                <option value="{{ $key }}" {{ old('exam_type') == $key ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('exam_type'))
                        <p class="field-error">{{ $errors->first('exam_type') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="exam_date">Exam Date</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar icon"></i>

                        <input type="date"
                               name="exam_date"
                               id="exam_date"
                               value="{{ old('exam_date', date('Y-m-d')) }}"
                               class="field-input {{ $errors->has('exam_date') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('exam_date'))
                        <p class="field-error">{{ $errors->first('exam_date') }}</p>
                    @endif
                </div>

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
                        <p class="field-error">{{ $errors->first('start_time') }}</p>
                    @endif
                </div>

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
                        <p class="field-error">{{ $errors->first('end_time') }}</p>
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
                    <p class="form-card-subtitle">Select branch, course, batch and subject</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="branch_id">Branch</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-school icon"></i>

                        <select name="branch_id" id="branch_id" class="field-input">
                            @foreach($branches as $id => $branch)
                                <option value="{{ $id }}" {{ old('branch_id') == $id ? 'selected' : '' }}>
                                    {{ $branch }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="course_id">Course</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-book icon"></i>

                        <select name="course_id" id="course_id" class="field-input">
                            @foreach($courses as $id => $course)
                                <option value="{{ $id }}" {{ old('course_id') == $id ? 'selected' : '' }}>
                                    {{ $course }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="batch_id">Batch</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-users icon"></i>

                        <select name="batch_id" id="batch_id" class="field-input">
                            @foreach($batches as $id => $batch)
                                <option value="{{ $id }}" {{ old('batch_id') == $id ? 'selected' : '' }}>
                                    {{ $batch }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="subject_id">Subject</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-book-open icon"></i>

                        <select name="subject_id" id="subject_id" class="field-input">
                            @foreach($subjects as $id => $subject)
                                <option value="{{ $id }}" {{ old('subject_id') == $id ? 'selected' : '' }}>
                                    {{ $subject }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

            </div>
        </div>

        <div class="form-card" style="grid-column:1/-1;">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-star"></i>
                </div>

                <div>
                    <p class="form-card-title">Marks & Status</p>
                    <p class="form-card-subtitle">Set total marks, passing marks and status</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="field-group">
                            <label class="field-label" for="total_marks">
                                Total Marks <span class="req">*</span>
                            </label>

                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="total_marks"
                                   id="total_marks"
                                   value="{{ old('total_marks', 100) }}"
                                   required
                                   class="field-input {{ $errors->has('total_marks') ? 'error' : '' }}">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="field-group">
                            <label class="field-label" for="passing_marks">
                                Passing Marks <span class="req">*</span>
                            </label>

                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="passing_marks"
                                   id="passing_marks"
                                   value="{{ old('passing_marks', 33) }}"
                                   required
                                   class="field-input {{ $errors->has('passing_marks') ? 'error' : '' }}">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="field-group">
                            <label class="field-label" for="status">
                                Status <span class="req">*</span>
                            </label>

                            <select name="status" id="status" required class="field-input">
                                <option value="scheduled" {{ old('status', 'scheduled') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="remarks">Remarks</label>

                    <textarea name="remarks"
                              id="remarks"
                              rows="5"
                              placeholder="Enter exam remarks"
                              class="field-input {{ $errors->has('remarks') ? 'error' : '' }}">{{ old('remarks') }}</textarea>
                </div>
            </div>
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">
            <i class="fas fa-check"></i>
            {{ trans('global.save') }}
        </button>

        <a href="{{ route('admin.exams.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>
</form>

@endsection