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
                <label class="field-label">Date <span class="req">*</span></label>
                <input type="date" name="date" value="{{ $val('date', request('date')) ? \Illuminate\Support\Carbon::parse($val('date', request('date')))->format('Y-m-d') : '' }}" required class="field-input {{ $errors->has('date') ? 'error' : '' }}">
                @if($errors->has('date')) <p class="field-error">{{ $errors->first('date') }}</p> @endif
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

<div class="form-actions">
    <button type="submit" class="btn-primary"><i class="fas fa-check"></i> {{ trans('global.save') }}</button>
    <a href="{{ route('admin.holidays.index') }}" class="btn-ghost">{{ trans('global.cancel') }}</a>
</div>
