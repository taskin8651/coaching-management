@php
    $isEdit = (bool) $academicYear;
@endphp

<div class="admin-form-grid">
    <div class="form-card" style="grid-column: 1 / -1;">
        <div class="form-card-header">
            <div class="form-card-icon"><i class="fas fa-calendar-alt"></i></div>
            <div>
                <p class="form-card-title">Academic Year Details</p>
                <p class="form-card-subtitle">Name, dates, current flag and status</p>
            </div>
        </div>

        <div class="form-card-body">
            <div class="field-group">
                <label class="field-label">Academic Year <span class="req">*</span></label>
                <input type="text"
                       name="name"
                       value="{{ old('name', $isEdit ? $academicYear->name : '') }}"
                       required
                       placeholder="Example: 2026-27"
                       class="field-input {{ $errors->has('name') ? 'error' : '' }}">
                @if($errors->has('name')) <p class="field-error">{{ $errors->first('name') }}</p> @endif
            </div>

            <div class="field-group">
                <label class="field-label">Start Date</label>
                <input type="date"
                       name="start_date"
                       value="{{ old('start_date', $isEdit ? optional($academicYear->start_date)->format('Y-m-d') : '') }}"
                       class="field-input {{ $errors->has('start_date') ? 'error' : '' }}">
                @if($errors->has('start_date')) <p class="field-error">{{ $errors->first('start_date') }}</p> @endif
            </div>

            <div class="field-group">
                <label class="field-label">End Date</label>
                <input type="date"
                       name="end_date"
                       value="{{ old('end_date', $isEdit ? optional($academicYear->end_date)->format('Y-m-d') : '') }}"
                       class="field-input {{ $errors->has('end_date') ? 'error' : '' }}">
                @if($errors->has('end_date')) <p class="field-error">{{ $errors->first('end_date') }}</p> @endif
            </div>

            <div class="field-group">
                <label class="field-label">Status <span class="req">*</span></label>
                <select name="status" class="field-input">
                    <option value="active" {{ old('status', $isEdit ? $academicYear->status : 'active') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $isEdit ? $academicYear->status : '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="field-group" style="grid-column: 1 / -1;">
                <label class="field-label">
                    <input type="checkbox" name="is_current" value="1" {{ old('is_current', $isEdit ? $academicYear->is_current : false) ? 'checked' : '' }}>
                    Set as current academic year
                </label>
                <p class="field-hint">Only one academic year can be current at a time.</p>
            </div>
        </div>
    </div>
</div>

<div class="form-actions">
    <button type="submit" class="btn-primary"><i class="fas fa-check"></i> {{ trans('global.save') }}</button>
    <a href="{{ route('admin.academic-years.index') }}" class="btn-ghost">{{ trans('global.cancel') }}</a>
</div>
