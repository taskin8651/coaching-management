@extends('layouts.admin')

@section('page-title', 'Add Duty Schedule')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.staff-timetables.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Add Duty Schedule</h2>

        <p class="admin-page-subtitle">
            Assign a staff member's duty day/date, timing and location
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.staff-timetables.store') }}">
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-user-clock"></i>
                </div>

                <div>
                    <p class="form-card-title">Staff & Branch</p>
                    <p class="form-card-subtitle">Who this duty schedule is for</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="staff_id">
                        Staff <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-user icon"></i>

                        <select name="staff_id" id="staff_id" required class="field-input {{ $errors->has('staff_id') ? 'error' : '' }}">
                            @foreach($staffMembers as $id => $name)
                                <option value="{{ $id }}" {{ old('staff_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('staff_id'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('staff_id') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="branch_id">Branch</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-code-branch icon"></i>

                        <select name="branch_id" id="branch_id" class="field-input {{ $errors->has('branch_id') ? 'error' : '' }}">
                            <option value="">Select Branch</option>
                            @foreach($branches as $id => $name)
                                <option value="{{ $id }}" {{ old('branch_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('branch_id'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('branch_id') }}</p>
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
                    <p class="form-card-subtitle">Set day/date, timing and location</p>
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
                                <option value="{{ $day }}" {{ old('day_of_week') == $day ? 'selected' : '' }}>{{ $day }}</option>
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
                        <p class="field-hint"><i class="fas fa-info-circle"></i> Leave blank for a regular weekly duty</p>
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
                    <label class="field-label" for="location">Location</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-door-open icon"></i>

                        <input type="text"
                               name="location"
                               id="location"
                               value="{{ old('location') }}"
                               placeholder="Example: Front Desk, Accounts Office"
                               class="field-input {{ $errors->has('location') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('location'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('location') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="status">Status</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-toggle-on icon"></i>

                        <select name="status" id="status" class="field-input {{ $errors->has('status') ? 'error' : '' }}">
                            <option value="scheduled" {{ old('status', 'scheduled') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    @if($errors->has('status'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('status') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="remarks">Remarks</label>

                    <textarea name="remarks" id="remarks" rows="3" class="field-input {{ $errors->has('remarks') ? 'error' : '' }}">{{ old('remarks') }}</textarea>

                    @if($errors->has('remarks'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('remarks') }}</p>
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

        <a href="{{ route('admin.staff-timetables.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</form>

@endsection
