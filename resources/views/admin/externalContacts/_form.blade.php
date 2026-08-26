@php
    $isEdit = (bool) $externalContact;
    $val = fn ($field, $default = null) => old($field, $isEdit ? $externalContact->{$field} : $default);
@endphp

<div class="admin-form-grid">

    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-icon"><i class="fas fa-user"></i></div>
            <div>
                <p class="form-card-title">Personal Details</p>
                <p class="form-card-subtitle">Basic identity — not turned into a Karmayoga student record</p>
            </div>
        </div>

        <div class="form-card-body">
            <div class="field-group">
                <label class="field-label">Full Name <span class="req">*</span></label>
                <input type="text" name="name" value="{{ $val('name') }}" required class="field-input {{ $errors->has('name') ? 'error' : '' }}">
                @if($errors->has('name')) <p class="field-error">{{ $errors->first('name') }}</p> @endif
            </div>

            <div class="field-group">
                <label class="field-label">Gender</label>
                <select name="gender" class="field-input">
                    <option value="">-</option>
                    <option value="male" {{ $val('gender') == 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ $val('gender') == 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ $val('gender') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <div class="field-group">
                <label class="field-label">Date of Birth</label>
                <input type="date" name="date_of_birth" value="{{ $val('date_of_birth') ? \Illuminate\Support\Carbon::parse($val('date_of_birth'))->format('Y-m-d') : '' }}" class="field-input">
            </div>

            <div class="field-group">
                <label class="field-label">Standard / Class</label>
                <input type="text" name="standard" value="{{ $val('standard') }}" class="field-input">
            </div>

            <div class="field-group">
                <label class="field-label">School Name</label>
                <input type="text" name="school_name" value="{{ $val('school_name') }}" class="field-input">
            </div>
        </div>
    </div>

    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-icon"><i class="fas fa-address-book"></i></div>
            <div>
                <p class="form-card-title">Contact Details</p>
            </div>
        </div>

        <div class="form-card-body">
            <div class="field-group">
                <label class="field-label">Mobile <span class="req">*</span></label>
                <input type="text" name="mobile" value="{{ $val('mobile') }}" required class="field-input {{ $errors->has('mobile') ? 'error' : '' }}">
                @if($errors->has('mobile')) <p class="field-error">{{ $errors->first('mobile') }}</p> @endif
            </div>

            <div class="field-group">
                <label class="field-label">WhatsApp Number</label>
                <input type="text" name="whatsapp_number" value="{{ $val('whatsapp_number') }}" class="field-input">
            </div>

            <div class="field-group">
                <label class="field-label">Email</label>
                <input type="email" name="email" value="{{ $val('email') }}" class="field-input {{ $errors->has('email') ? 'error' : '' }}">
                @if($errors->has('email')) <p class="field-error">{{ $errors->first('email') }}</p> @endif
            </div>

            <div class="field-group">
                <label class="field-label">City</label>
                <input type="text" name="city" value="{{ $val('city') }}" class="field-input">
            </div>

            <div class="field-group">
                <label class="field-label">Area</label>
                <input type="text" name="area" value="{{ $val('area') }}" class="field-input">
            </div>
        </div>
    </div>

    <div class="form-card" style="grid-column: 1 / -1;">
        <div class="form-card-header">
            <div class="form-card-icon"><i class="fas fa-user-friends"></i></div>
            <div>
                <p class="form-card-title">Parent / Guardian</p>
            </div>
        </div>

        <div class="form-card-body">
            <div class="field-group">
                <label class="field-label">Guardian Name</label>
                <input type="text" name="guardian_name" value="{{ $val('guardian_name') }}" class="field-input">
            </div>
            <div class="field-group">
                <label class="field-label">Guardian Mobile</label>
                <input type="text" name="guardian_mobile" value="{{ $val('guardian_mobile') }}" class="field-input">
            </div>
            <div class="field-group">
                <label class="field-label">Guardian Email</label>
                <input type="email" name="guardian_email" value="{{ $val('guardian_email') }}" class="field-input">
            </div>
            <div class="field-group" style="grid-column: 1 / -1;">
                <label class="field-label">Remarks</label>
                <textarea name="remarks" rows="3" class="field-input">{{ $val('remarks') }}</textarea>
            </div>
        </div>
    </div>

</div>

<div class="form-actions">
    <button type="submit" class="btn-primary"><i class="fas fa-check"></i> {{ trans('global.save') }}</button>
    <a href="{{ route('admin.external-contacts.index') }}" class="btn-ghost">{{ trans('global.cancel') }}</a>
</div>
