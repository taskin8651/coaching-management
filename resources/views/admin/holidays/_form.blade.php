@php
    $isEdit = (bool) $holiday;
    $val = fn ($field, $default = null) => old($field, $isEdit ? $holiday->{$field} : $default);
    $isAdmin = $scope['is_admin'] ?? false;
@endphp

<div class="admin-form-grid">

    <div class="form-card" style="grid-column: 1 / -1;">
        <div class="form-card-header">
            <div class="form-card-icon"><i class="fas fa-umbrella-beach"></i></div>
            <div>
                <p class="form-card-title">Holiday Details</p>
            </div>
        </div>

        <div class="form-card-body">
            <div class="field-group">
                <label class="field-label">Holiday Name <span class="req">*</span></label>
                <input type="text" name="name" value="{{ $val('name') }}" required placeholder="Example: Diwali, Independence Day" class="field-input {{ $errors->has('name') ? 'error' : '' }}">
                @if($errors->has('name')) <p class="field-error">{{ $errors->first('name') }}</p> @endif
            </div>

            <div class="field-group">
                <label class="field-label">From <span class="req">*</span></label>
                <input type="date" name="start_date" id="holiday_start_date" value="{{ $val('start_date', request('date')) ? \Illuminate\Support\Carbon::parse($val('start_date', request('date')))->format('Y-m-d') : '' }}" required class="field-input {{ $errors->has('start_date') ? 'error' : '' }}">
                @if($errors->has('start_date')) <p class="field-error">{{ $errors->first('start_date') }}</p> @endif
            </div>

            <div class="field-group">
                <label class="field-label">To <span class="req">*</span></label>
                <input type="date" name="end_date" id="holiday_end_date" value="{{ $val('end_date', request('date')) ? \Illuminate\Support\Carbon::parse($val('end_date', request('date')))->format('Y-m-d') : '' }}" required min="{{ $val('start_date', request('date')) ? \Illuminate\Support\Carbon::parse($val('start_date', request('date')))->format('Y-m-d') : '' }}" class="field-input {{ $errors->has('end_date') ? 'error' : '' }}">
                @if($errors->has('end_date')) <p class="field-error">{{ $errors->first('end_date') }}</p> @endif
            </div>

            <div class="field-group">
                <label class="field-label">Branch</label>
                @if($isAdmin)
                    <select name="branch_id" class="field-input">
                        @foreach($branches as $id => $branch)
                            <option value="{{ $id }}" {{ $val('branch_id') == $id ? 'selected' : '' }}>{{ $branch }}</option>
                        @endforeach
                    </select>
                    <p class="field-hint">Leave as "All Branches" for an institute-wide holiday.</p>
                @else
                    <input type="text" class="field-input" value="{{ $branches->get($scope['branch_id']) ?? 'Your Branch' }}" disabled>
                    <p class="field-hint">Branch Managers can only add holidays for their own branch.</p>
                @endif
            </div>

            <div class="field-group">
                <label class="field-label">Type <span class="req">*</span></label>
                <select name="type" required class="field-input">
                    <option value="mandatory" {{ $val('type', 'mandatory') == 'mandatory' ? 'selected' : '' }}>Mandatory</option>
                    <option value="optional" {{ $val('type') == 'optional' ? 'selected' : '' }}>Optional (Restricted)</option>
                </select>
                <p class="field-hint">Only Mandatory holidays reduce the working-days count used in salary calculation.</p>
            </div>

            <div class="field-group" style="grid-column: 1 / -1;">
                <label class="field-label">Description</label>
                <textarea name="description" rows="3" class="field-input">{{ $val('description') }}</textarea>
            </div>
        </div>
    </div>

</div>

<script>
document.getElementById('holiday_start_date').addEventListener('change', function () {
    const endDate = document.getElementById('holiday_end_date');
    endDate.min = this.value;
    if (endDate.value && endDate.value < this.value) endDate.value = this.value;
});
</script>

<div class="form-actions">
    <button type="submit" class="btn-primary"><i class="fas fa-check"></i> {{ trans('global.save') }}</button>
    <a href="{{ route('admin.holidays.index') }}" class="btn-ghost">{{ trans('global.cancel') }}</a>
</div>
