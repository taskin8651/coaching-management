@extends('layouts.admin')

@section('page-title', trans('global.my_profile'))

@section('content')
<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">{{ trans('global.my_profile') }}</h2>
        <p class="admin-page-subtitle">Update your account details and password securely.</p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
    <div class="page-card">
        <div class="page-card-header">
            <p class="page-card-title">
                <i class="fas fa-user-circle mr-2"></i>
                {{ trans('global.my_profile') }}
            </p>

            <span class="page-card-note">Account information</span>
        </div>

        <form method="POST" action="{{ route('profile.password.updateProfile') }}" class="admin-form">
            @csrf

            <div class="admin-form-grid">
                <div class="field-group">
                    <label class="field-label required" for="name">{{ trans('cruds.user.fields.name') }}</label>

                    <input class="field-input {{ $errors->has('name') ? 'error' : '' }}"
                           type="text"
                           name="name"
                           id="name"
                           value="{{ old('name', auth()->user()->name) }}"
                           required>

                    @if($errors->has('name'))
                        <p class="field-error">{{ $errors->first('name') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label required" for="email">{{ trans('cruds.user.fields.email') }}</label>

                    <input class="field-input {{ $errors->has('email') ? 'error' : '' }}"
                           type="email"
                           name="email"
                           id="email"
                           value="{{ old('email', auth()->user()->email) }}"
                           required>

                    @if($errors->has('email'))
                        <p class="field-error">{{ $errors->first('email') }}</p>
                    @endif
                </div>
            </div>

            <div class="form-actions">
                <button class="btn-primary" type="submit">
                    <i class="fas fa-save"></i>
                    {{ trans('global.save') }}
                </button>
            </div>
        </form>
    </div>

    <div class="page-card">
        <div class="page-card-header">
            <p class="page-card-title">
                <i class="fas fa-key mr-2"></i>
                {{ trans('global.change_password') }}
            </p>

            <span class="page-card-note">Use a strong password</span>
        </div>

        <form method="POST" action="{{ route('profile.password.update') }}" class="admin-form">
            @csrf

            <div class="admin-form-grid">
                <div class="field-group">
                    <label class="field-label required" for="password">New {{ trans('cruds.user.fields.password') }}</label>

                    <input class="field-input {{ $errors->has('password') ? 'error' : '' }}"
                           type="password"
                           name="password"
                           id="password"
                           required>

                    @if($errors->has('password'))
                        <p class="field-error">{{ $errors->first('password') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label required" for="password_confirmation">Repeat New {{ trans('cruds.user.fields.password') }}</label>

                    <input class="field-input"
                           type="password"
                           name="password_confirmation"
                           id="password_confirmation"
                           required>
                </div>
            </div>

            <div class="form-actions">
                <button class="btn-primary" type="submit">
                    <i class="fas fa-lock"></i>
                    {{ trans('global.save') }}
                </button>
            </div>
        </form>
    </div>
</div>

<div class="page-card mt-5">
    <div class="page-card-header">
        <p class="page-card-title text-red-600">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            {{ trans('global.delete_account') }}
        </p>

        <span class="page-card-note">Permanent action</span>
    </div>

    <form method="POST"
          action="{{ route('profile.password.destroyProfile') }}"
          class="admin-form"
          onsubmit="return prompt('{{ __('global.delete_account_warning') }}') == '{{ auth()->user()->email }}'">
        @csrf

        <div class="form-info-box" style="background:#fff1f2;border-color:#fecdd3;color:#9f1239;">
            <p><i class="fas fa-info-circle"></i> Type your email address in the confirmation prompt to delete this account.</p>
        </div>

        <div class="form-actions">
            <button class="btn-danger" type="submit">
                <i class="fas fa-trash"></i>
                {{ trans('global.delete') }}
            </button>
        </div>
    </form>
</div>
@endsection
