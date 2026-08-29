@extends('layouts.admin')

@section('page-title', 'Edit Branch')

@section('content')

@php
    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
@endphp

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.branches.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Edit Branch</h2>

        <p class="admin-page-subtitle">
            Update branch details and manager assignment
        </p>
    </div>

    <div class="identity-card">
        @if($branch->logo)
            <img src="{{ $branch->logo }}"
                 alt="{{ $branch->name }}"
                 class="identity-avatar"
                 style="object-fit:cover;">
        @else
            <div class="identity-avatar" style="background: {{ $colors[$branch->id % count($colors)] }};">
                {{ strtoupper(substr($branch->name, 0, 1)) }}
            </div>
        @endif

        <div>
            <p class="identity-title">{{ $branch->name }}</p>
            <p class="identity-subtitle">ID #{{ $branch->id }}</p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.branches.update', $branch->id) }}" enctype="multipart/form-data">
    @method('PUT')
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-building"></i>
                </div>

                <div>
                    <p class="form-card-title">Branch Information</p>
                    <p class="form-card-subtitle">Update branch details</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="name">
                        Branch Name <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-building icon"></i>

                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name', $branch->name) }}"
                               required
                               class="field-input {{ $errors->has('name') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('name'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('name') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="branch_code">
                        Branch Code
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-barcode icon"></i>

                        <input type="text"
                               name="branch_code"
                               id="branch_code"
                               value="{{ old('branch_code', $branch->branch_code) }}"
                               class="field-input {{ $errors->has('branch_code') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('branch_code'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('branch_code') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="logo">
                        Branch Logo
                    </label>

                    @if($branch->logo)
                        <div class="mb-2">
                            <img src="{{ $branch->logo }}"
                                 alt="{{ $branch->name }}"
                                 style="width:90px; height:90px; object-fit:cover; border-radius:18px; border:1px solid #E2E8F0;">
                        </div>
                    @endif

                    <div class="input-icon-wrap">
                        <i class="fas fa-image icon"></i>

                        <input type="file"
                               name="logo"
                               id="logo"
                               accept="image/*"
                               class="field-input {{ $errors->has('logo') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('logo'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('logo') }}
                        </p>
                    @else
                        <p class="field-hint">Upload new logo only if you want to replace old logo.</p>
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
                            <option value="active" {{ old('status', $branch->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $branch->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    @if($errors->has('status'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('status') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="weekly_off_day">
                        Weekly Off Day
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar-week icon"></i>

                        <select name="weekly_off_day"
                                id="weekly_off_day"
                                class="field-input {{ $errors->has('weekly_off_day') ? 'error' : '' }}">
                            <option value="">Sunday (default)</option>
                            @foreach(['0' => 'Sunday', '1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday'] as $val => $label)
                                <option value="{{ $val }}" {{ old('weekly_off_day', $branch->weekly_off_day) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('weekly_off_day'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('weekly_off_day') }}
                        </p>
                    @else
                        <p class="field-hint">Used to calculate working days for salary deductions.</p>
                    @endif
                </div>

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-user-tie"></i>
                </div>

                <div>
                    <p class="form-card-title">Manager & Contact</p>
                    <p class="form-card-subtitle">Update manager and contact details</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="manager_id">
                        Branch Manager
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-user-tie icon"></i>

                        <select name="manager_id"
                                id="manager_id"
                                class="field-input {{ $errors->has('manager_id') ? 'error' : '' }}">
                            @foreach($managers as $id => $manager)
                                <option value="{{ $id }}" {{ old('manager_id', $branch->manager_id) == $id ? 'selected' : '' }}>
                                    {{ $manager }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('manager_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('manager_id') }}
                        </p>
                    @endif
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
                               value="{{ old('phone', $branch->phone) }}"
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
                    <label class="field-label" for="email">
                        Email
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-envelope icon"></i>

                        <input type="email"
                               name="email"
                               id="email"
                               value="{{ old('email', $branch->email) }}"
                               class="field-input {{ $errors->has('email') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('email'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('email') }}
                        </p>
                    @endif
                </div>

                <div class="form-info-box">
                    <p class="meta-label">Branch Info</p>

                    <div class="meta-grid-2">
                        <div>
                            <p class="meta-small-label">Created</p>
                            <p class="meta-value-strong">
                                {{ optional($branch->created_at)->format('d M Y') ?? '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="meta-small-label">Status</p>

                            @if($branch->status == 'active')
                                <p class="meta-value-strong meta-value-success">
                                    <i class="fas fa-check-circle"></i>
                                    Active
                                </p>
                            @else
                                <p class="meta-value-strong meta-value-warning">
                                    <i class="fas fa-clock"></i>
                                    Inactive
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="form-card" style="grid-column: 1 / -1;">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>

                <div>
                    <p class="form-card-title">Address Details</p>
                    <p class="form-card-subtitle">Update branch location</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="field-group">
                    <label class="field-label" for="address">
                        Address
                    </label>

                    <textarea name="address"
                              id="address"
                              rows="4"
                              class="field-input {{ $errors->has('address') ? 'error' : '' }}">{{ old('address', $branch->address) }}</textarea>

                    @if($errors->has('address'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('address') }}
                        </p>
                    @endif
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="field-group">
                            <label class="field-label" for="city">City</label>

                            <div class="input-icon-wrap">
                                <i class="fas fa-city icon"></i>

                                <input type="text"
                                       name="city"
                                       id="city"
                                       value="{{ old('city', $branch->city) }}"
                                       class="field-input {{ $errors->has('city') ? 'error' : '' }}">
                            </div>

                            @if($errors->has('city'))
                                <p class="field-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $errors->first('city') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="field-group">
                            <label class="field-label" for="state">State</label>

                            <div class="input-icon-wrap">
                                <i class="fas fa-map icon"></i>

                                <input type="text"
                                       name="state"
                                       id="state"
                                       value="{{ old('state', $branch->state) }}"
                                       class="field-input {{ $errors->has('state') ? 'error' : '' }}">
                            </div>

                            @if($errors->has('state'))
                                <p class="field-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $errors->first('state') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="field-group">
                            <label class="field-label" for="pincode">Pincode</label>

                            <div class="input-icon-wrap">
                                <i class="fas fa-map-pin icon"></i>

                                <input type="text"
                                       name="pincode"
                                       id="pincode"
                                       value="{{ old('pincode', $branch->pincode) }}"
                                       class="field-input {{ $errors->has('pincode') ? 'error' : '' }}">
                            </div>

                            @if($errors->has('pincode'))
                                <p class="field-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $errors->first('pincode') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">
            <i class="fas fa-check"></i>
            {{ trans('global.save') }}
        </button>

        <a href="{{ route('admin.branches.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</form>

@endsection