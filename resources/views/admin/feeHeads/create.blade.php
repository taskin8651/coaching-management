@extends('layouts.admin')

@section('page-title', 'Add Fee Head')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.fee-heads.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">Add Fee Head</h2>
        <p class="admin-page-subtitle">Create a new fee head to use inside fee structures</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.fee-heads.store') }}">
    @csrf

    <div class="admin-form-grid">
        <div class="form-card" style="grid-column: 1 / -1;">
            <div class="form-card-header">
                <div class="form-card-icon"><i class="fas fa-tags"></i></div>
                <div>
                    <p class="form-card-title">Fee Head Information</p>
                    <p class="form-card-subtitle">Name, code, GST and status</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="field-group">
                    <label class="field-label">Fee Head Name <span class="req">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Example: Tuition Fee, Admission Fee" class="field-input {{ $errors->has('name') ? 'error' : '' }}">
                    @if($errors->has('name')) <p class="field-error">{{ $errors->first('name') }}</p> @endif
                </div>

                <div class="field-group">
                    <label class="field-label">Fee Code <span class="req">*</span></label>
                    <input type="text" name="code" value="{{ old('code') }}" required placeholder="Example: tuition" class="field-input {{ $errors->has('code') ? 'error' : '' }}">
                    @if($errors->has('code'))
                        <p class="field-error">{{ $errors->first('code') }}</p>
                    @else
                        <p class="field-hint">Unique internal code, should not be reused elsewhere.</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label">Status <span class="req">*</span></label>
                    <select name="status" class="field-input">
                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label">
                        <input type="checkbox" name="gst_applicable" value="1" id="gst_applicable" {{ old('gst_applicable') ? 'checked' : '' }}>
                        GST Applicable
                    </label>
                </div>

                <div class="field-group">
                    <label class="field-label">Default GST %</label>
                    <input type="number" step="0.01" min="0" max="100" name="default_gst_percent" value="{{ old('default_gst_percent', 0) }}" class="field-input">
                    <p class="field-hint">Used as the default when this fee head is added to a fee structure; can be overridden per structure.</p>
                </div>

                <div class="field-group" style="grid-column: 1 / -1;">
                    <label class="field-label">Description</label>
                    <textarea name="description" rows="3" class="field-input">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary"><i class="fas fa-check"></i> {{ trans('global.save') }}</button>
        <a href="{{ route('admin.fee-heads.index') }}" class="btn-ghost">{{ trans('global.cancel') }}</a>
    </div>
</form>

@endsection
