@extends('layouts.admin')

@section('page-title', 'Edit Staff')

@section('content')

@php
    $name = $staff->user->name ?? 'Staff';
    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
@endphp

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.staff.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Edit Staff</h2>

        <p class="admin-page-subtitle">
            Update staff profile, salary, photo and documents
        </p>
    </div>

    <div class="identity-card">
        @if($staff->photo)
            <img src="{{ $staff->photo }}"
                 alt="{{ $name }}"
                 class="identity-avatar"
                 style="object-fit:cover;">
        @else
            <div class="identity-avatar" style="background: {{ $colors[$staff->id % count($colors)] }};">
                {{ strtoupper(substr($name, 0, 1)) }}
            </div>
        @endif

        <div>
            <p class="identity-title">{{ $name }}</p>
            <p class="identity-subtitle">ID #{{ $staff->id }}</p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.staff.update', $staff->id) }}" enctype="multipart/form-data">
    @method('PUT')
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-user-tie"></i>
                </div>

                <div>
                    <p class="form-card-title">Staff Information</p>
                    <p class="form-card-subtitle">Update basic staff details</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="user_id">
                        User Account
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-user icon"></i>

                        <select name="user_id"
                                id="user_id"
                                class="field-input {{ $errors->has('user_id') ? 'error' : '' }}">
                            @foreach($users as $id => $user)
                                <option value="{{ $id }}" {{ old('user_id', $staff->user_id) == $id ? 'selected' : '' }}>
                                    {{ $user }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('user_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('user_id') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="branch_id">
                        Branch
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-school icon"></i>

                        <select name="branch_id"
                                id="branch_id"
                                class="field-input {{ $errors->has('branch_id') ? 'error' : '' }}">
                            @foreach($branches as $id => $branch)
                                <option value="{{ $id }}" {{ old('branch_id', $staff->branch_id) == $id ? 'selected' : '' }}>
                                    {{ $branch }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('branch_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('branch_id') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="biometric_id">Biometric Code</label>
                    <div class="input-icon-wrap"><i class="fas fa-fingerprint icon"></i><input type="text" name="biometric_id" id="biometric_id" value="{{ old('biometric_id', $staff->biometric_id) }}" placeholder="BIO-001" class="field-input {{ $errors->has('biometric_id') ? 'error' : '' }}"></div>
                    @if($errors->has('biometric_id')) <p class="field-error">{{ $errors->first('biometric_id') }}</p> @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="phone">
                        Phone
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-phone icon"></i>

                        <input type="text"
                               name="phone"
                               id="phone"
                               value="{{ old('phone', $staff->phone) }}"
                               class="field-input {{ $errors->has('phone') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('phone'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('phone') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="alternate_phone">
                        Alternate Phone
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-phone-alt icon"></i>

                        <input type="text"
                               name="alternate_phone"
                               id="alternate_phone"
                               value="{{ old('alternate_phone', $staff->alternate_phone) }}"
                               class="field-input {{ $errors->has('alternate_phone') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('alternate_phone'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('alternate_phone') }}
                        </p>
                    @endif
                </div>

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-briefcase"></i>
                </div>

                <div>
                    <p class="form-card-title">Job Details</p>
                    <p class="form-card-subtitle">Update designation, department and salary</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="designation">
                        Designation
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-id-badge icon"></i>

                        <input type="text"
                               name="designation"
                               id="designation"
                               value="{{ old('designation', $staff->designation) }}"
                               class="field-input {{ $errors->has('designation') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('designation'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('designation') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="department">
                        Department
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-building icon"></i>

                        <input type="text"
                               name="department"
                               id="department"
                               value="{{ old('department', $staff->department) }}"
                               class="field-input {{ $errors->has('department') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('department'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('department') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="salary">
                        Salary
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-rupee-sign icon"></i>

                        <input type="number"
                               step="0.01"
                               min="0"
                               name="salary"
                               id="salary"
                               value="{{ old('salary', $staff->salary) }}"
                               class="field-input {{ $errors->has('salary') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('salary'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('salary') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="joining_date">
                        Joining Date
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar icon"></i>

                        <input type="date"
                               name="joining_date"
                               id="joining_date"
                               value="{{ old('joining_date', optional($staff->joining_date)->format('Y-m-d')) }}"
                               class="field-input {{ $errors->has('joining_date') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('joining_date'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('joining_date') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="status">
                        Status <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-toggle-on icon"></i>

                        <select name="status"
                                id="status"
                                required
                                class="field-input {{ $errors->has('status') ? 'error' : '' }}">
                            <option value="active" {{ old('status', $staff->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $staff->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    @if($errors->has('status'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('status') }}
                        </p>
                    @endif
                </div>

            </div>
        </div>

        <div class="form-card" style="grid-column: 1 / -1;">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-file-upload"></i>
                </div>

                <div>
                    <p class="form-card-title">Photo & Documents</p>
                    <p class="form-card-subtitle">Update staff photo and documents</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="field-group">
                            <label class="field-label" for="photo">
                                Staff Photo
                            </label>

                            @if($staff->photo)
                                <div class="mb-2">
                                    <img src="{{ $staff->photo }}"
                                         alt="{{ $name }}"
                                         style="width:90px; height:90px; object-fit:cover; border-radius:18px; border:1px solid #E2E8F0;">
                                </div>
                            @endif

                            <div class="input-icon-wrap">
                                <i class="fas fa-image icon"></i>

                                <input type="file"
                                       name="photo"
                                       id="photo"
                                       accept="image/*"
                                       class="field-input {{ $errors->has('photo') ? 'error' : '' }}">
                            </div>

                            @if($errors->has('photo'))
                                <p class="field-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $errors->first('photo') }}
                                </p>
                            @else
                                <p class="field-hint">Upload new photo only if you want to replace old photo.</p>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="field-group">
                            <label class="field-label" for="documents">
                                Add More Documents
                            </label>

                            <div class="input-icon-wrap">
                                <i class="fas fa-file icon"></i>

                                <input type="file"
                                       name="documents[]"
                                       id="documents"
                                       multiple
                                       class="field-input {{ $errors->has('documents') ? 'error' : '' }}">
                            </div>

                            @if($errors->has('documents'))
                                <p class="field-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $errors->first('documents') }}
                                </p>
                            @else
                                <p class="field-hint">New documents will be added with existing documents.</p>
                            @endif
                        </div>

                        @if($staff->documents && count($staff->documents))
                            <div class="form-info-box">
                                <p class="meta-label">Uploaded Documents</p>

                                @foreach($staff->documents as $document)
                                    <p style="margin:6px 0;">
                                        <i class="fas fa-file"></i>
                                        <a href="{{ $document['url'] }}" target="_blank">
                                            {{ $document['name'] }}
                                        </a>
                                    </p>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="address">
                        Address
                    </label>

                    <textarea name="address"
                              id="address"
                              rows="5"
                              class="field-input {{ $errors->has('address') ? 'error' : '' }}">{{ old('address', $staff->address) }}</textarea>

                    @if($errors->has('address'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('address') }}
                        </p>
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

        <a href="{{ route('admin.staff.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</form>

@endsection
