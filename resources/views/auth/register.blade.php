@extends('layouts.app')

@section('title', trans('global.register') . ' | ' . trans('panel.site_title'))

@section('content')
<div class="auth-card">

    <div class="auth-card-head">
        <div class="auth-card-top">
            <i class="fas fa-user-plus"></i>
            New Registration
        </div>

        <h2>{{ trans('global.register') }}</h2>
        <p>Create your account to access institute dashboard and services.</p>
    </div>

    @if($errors->any())
        <div class="auth-alert auth-alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span>Please check the entered information.</span>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf

        <div class="auth-grid-2">
            <div class="auth-field">
                <label for="name">{{ trans('global.user_name') }} <span class="req">*</span></label>

                <div class="auth-input-wrap">
                    <i class="fas fa-user"></i>

                    <input type="text"
                           name="name"
                           id="name"
                           value="{{ old('name') }}"
                           required
                           autofocus
                           placeholder="Enter full name"
                           class="{{ $errors->has('name') ? 'error' : '' }}">
                </div>

                @if($errors->has('name'))
                    <p class="auth-error">{{ $errors->first('name') }}</p>
                @endif
            </div>

            <div class="auth-field">
                <label for="phone">Mobile Number <span class="req">*</span></label>

                <div class="auth-input-wrap">
                    <i class="fas fa-phone"></i>

                    <input type="text"
                           name="phone"
                           id="phone"
                           value="{{ old('phone') }}"
                           required
                           placeholder="Enter mobile number"
                           class="{{ $errors->has('phone') ? 'error' : '' }}">
                </div>

                @if($errors->has('phone'))
                    <p class="auth-error">{{ $errors->first('phone') }}</p>
                @endif
            </div>
        </div>

        <div class="auth-field">
            <label for="email">{{ trans('global.login_email') }} <span class="req">*</span></label>

            <div class="auth-input-wrap">
                <i class="fas fa-envelope"></i>

                <input type="email"
                       name="email"
                       id="email"
                       value="{{ old('email') }}"
                       required
                       placeholder="Enter email address"
                       class="{{ $errors->has('email') ? 'error' : '' }}">
            </div>

            @if($errors->has('email'))
                <p class="auth-error">{{ $errors->first('email') }}</p>
            @endif
        </div>

        <div class="auth-grid-2">
            <div class="auth-field">
                <label for="branch_id">Select Branch <span class="req">*</span></label>

                <div class="auth-input-wrap">
                    <i class="fas fa-code-branch"></i>

                    <select name="branch_id"
                            id="branch_id"
                            required
                            class="{{ $errors->has('branch_id') ? 'error' : '' }}">
                        <option value="">Choose branch</option>
                        @foreach($branches as $id => $branch)
                            <option value="{{ $id }}" {{ old('branch_id') == $id ? 'selected' : '' }}>
                                {{ $branch }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($errors->has('branch_id'))
                    <p class="auth-error">{{ $errors->first('branch_id') }}</p>
                @endif
            </div>

            <div class="auth-field">
                <label for="role_id">Select Role <span class="req">*</span></label>

                <div class="auth-input-wrap">
                    <i class="fas fa-user-tag"></i>

                    <select name="role_id"
                            id="role_id"
                            required
                            class="{{ $errors->has('role_id') ? 'error' : '' }}">
                        <option value="">Choose role</option>
                        @foreach($roles as $id => $role)
                            <option value="{{ $id }}" {{ old('role_id') == $id ? 'selected' : '' }}>
                                {{ $role }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($errors->has('role_id'))
                    <p class="auth-error">{{ $errors->first('role_id') }}</p>
                @endif
            </div>
        </div>

        <div class="auth-grid-2">
            <div class="auth-field">
                <label for="password">{{ trans('global.login_password') }} <span class="req">*</span></label>

                <div class="auth-input-wrap has-eye">
                    <i class="fas fa-lock"></i>

                    <input type="password"
                           name="password"
                           id="password"
                           required
                           placeholder="Create password"
                           class="{{ $errors->has('password') ? 'error' : '' }}">

                    <button type="button" class="auth-eye" onclick="toggleAuthPassword('password', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>

                @if($errors->has('password'))
                    <p class="auth-error">{{ $errors->first('password') }}</p>
                @endif
            </div>

            <div class="auth-field">
                <label for="password_confirmation">{{ trans('global.login_password_confirmation') }} <span class="req">*</span></label>

                <div class="auth-input-wrap has-eye">
                    <i class="fas fa-shield-alt"></i>

                    <input type="password"
                           name="password_confirmation"
                           id="password_confirmation"
                           required
                           placeholder="Confirm password">

                    <button type="button" class="auth-eye" onclick="toggleAuthPassword('password_confirmation', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
        </div>

        <button type="submit" class="auth-submit">
            <i class="fas fa-user-plus"></i>
            {{ trans('global.register') }}
        </button>

        <div class="auth-bottom">
            Already have an account?
            <a href="{{ route('login') }}">Login here</a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
function toggleAuthPassword(id, btn) {
    const input = document.getElementById(id);
    const icon = btn.querySelector('i');

    if (!input) return;

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
@endsection
