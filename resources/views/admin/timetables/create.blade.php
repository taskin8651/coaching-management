@extends('layouts.admin')

@section('page-title', 'Add Timetable')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.timetables.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Add Timetable</h2>

        <p class="admin-page-subtitle">
            Create new class schedule with batch, subject, teacher and timing
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.timetables.store') }}">
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-layer-group"></i>
                </div>

                <div>
                    <p class="form-card-title">Academic Mapping</p>
                    <p class="form-card-subtitle">Select branch, course, batch, subject and teacher</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="branch_id">Branch</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-code-branch icon"></i>

                        <select name="branch_id" id="branch_id" class="field-input {{ $errors->has('branch_id') ? 'error' : '' }}">
                            <option value="">Select Branch</option>
                            @foreach($branches as $id => $name)
                                <option value="{{ $id }}" {{ old('branch_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('branch_id'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('branch_id') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="course_id">Course</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-book icon"></i>

                        <select name="course_id" id="course_id" class="field-input {{ $errors->has('course_id') ? 'error' : '' }}">
                            <option value="">Select Course</option>
                            @foreach($courses as $id => $name)
                                <option value="{{ $id }}" {{ old('course_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('course_id'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('course_id') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="batch_id">
                        Batch <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-users icon"></i>

                        <select name="batch_id" id="batch_id" required class="field-input {{ $errors->has('batch_id') ? 'error' : '' }}">
                            <option value="">Select Batch</option>
                            @foreach($batches as $id => $name)
                                <option value="{{ $id }}" {{ old('batch_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('batch_id'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('batch_id') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="subject_id">Subject</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-book-open icon"></i>

                        <select name="subject_id" id="subject_id" class="field-input {{ $errors->has('subject_id') ? 'error' : '' }}">
                            <option value="">Select Subject</option>
                            @foreach($subjects as $id => $name)
                                <option value="{{ $id }}" {{ old('subject_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('subject_id'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('subject_id') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="teacher_id">Teacher</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-chalkboard-teacher icon"></i>

                        <select name="teacher_id" id="teacher_id" class="field-input {{ $errors->has('teacher_id') ? 'error' : '' }}">
                            <option value="">Select Teacher</option>
                            @foreach($teachers as $id => $name)
                                <option value="{{ $id }}" {{ old('teacher_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('teacher_id'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('teacher_id') }}</p>
                    @endif
                </div>

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>

                <div>
                    <p class="form-card-title">Schedule Details</p>
                    <p class="form-card-subtitle">Set class day, date, time and room</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="day_of_week">Day</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar-day icon"></i>

                        <select name="day_of_week" id="day_of_week" class="field-input {{ $errors->has('day_of_week') ? 'error' : '' }}">
                            <option value="">Select Day</option>
                            @foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day)
                                <option value="{{ $day }}" {{ old('day_of_week') == $day ? 'selected' : '' }}>
                                    {{ $day }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('day_of_week'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('day_of_week') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="schedule_date">Specific Date</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar-check icon"></i>

                        <input type="date"
                               name="schedule_date"
                               id="schedule_date"
                               value="{{ old('schedule_date') }}"
                               class="field-input {{ $errors->has('schedule_date') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('schedule_date'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('schedule_date') }}</p>
                    @else
                        <p class="field-hint"><i class="fas fa-info-circle"></i> Leave blank for regular weekly schedule</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="start_time">
                        Start Time <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-clock icon"></i>

                        <input type="time"
                               name="start_time"
                               id="start_time"
                               required
                               value="{{ old('start_time') }}"
                               class="field-input {{ $errors->has('start_time') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('start_time'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('start_time') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="end_time">
                        End Time <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-hourglass-end icon"></i>

                        <input type="time"
                               name="end_time"
                               id="end_time"
                               required
                               value="{{ old('end_time') }}"
                               class="field-input {{ $errors->has('end_time') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('end_time'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('end_time') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="room">Room</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-door-open icon"></i>

                        <input type="text"
                               name="room"
                               id="room"
                               value="{{ old('room') }}"
                               placeholder="Example: Room 101"
                               class="field-input {{ $errors->has('room') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('room'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('room') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="status">Status</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-toggle-on icon"></i>

                        <select name="status" id="status" class="field-input {{ $errors->has('status') ? 'error' : '' }}">
                            <option value="scheduled" {{ old('status', 'scheduled') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="changed" {{ old('status') == 'changed' ? 'selected' : '' }}>Changed</option>
                            <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    @if($errors->has('status'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('status') }}</p>
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

        <a href="{{ route('admin.timetables.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</form>

@endsection