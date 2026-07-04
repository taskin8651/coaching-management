@extends('layouts.app')

@section('title', trans('global.reset_password') . ' | ' . trans('panel.site_title'))

@section('content')
<div class="auth-card">
    <div class="auth-card-head">
        <div class="auth-card-top">
            <i class="fas fa-key"></i>
            Create New Password
        </div>

        <h2>{{ trans('global.reset_password') }}</h2>
        <p>Set a new secure password for your institute account.</p>
    </div>

    @if($errors->any())
        <div class="auth-alert auth-alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span>Please check the details and try again.</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.request') }}" class="auth-form">
        @csrf

        <input name="token" value="{{ $token }}" type="hidden">

        <div class="auth-field">
            <label for="email">{{ trans('global.login_email') }} <span class="req">*</span></label>

            <div class="auth-input-wrap">
                <i class="fas fa-envelope"></i>

                <input id="email"
                       type="email"
                       name="email"
                       value="{{ $email ?? old('email') }}"
                       required
                       autocomplete="email"
                       autofocus
                       placeholder="Enter your email address"
                       class="{{ $errors->has('email') ? 'error' : '' }}">
            </div>

            @if($errors->has('email'))
                <p class="auth-error">{{ $errors->first('email') }}</p>
            @endif
        </div>

        <div class="auth-grid-2">
            <div class="auth-field">
                <label for="password">{{ trans('global.login_password') }} <span class="req">*</span></label>

                <div class="auth-input-wrap has-eye">
                    <i class="fas fa-lock"></i>

                    <input id="password"
                           type="password"
                           name="password"
                           required
                           autocomplete="new-password"
                           placeholder="Create new password"
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
                <label for="password-confirm">{{ trans('global.login_password_confirmation') }} <span class="req">*</span></label>

                <div class="auth-input-wrap has-eye">
                    <i class="fas fa-shield-alt"></i>

                    <input id="password-confirm"
                           type="password"
                           name="password_confirmation"
                           required
                           autocomplete="new-password"
                           placeholder="Confirm new password">

                    <button type="button" class="auth-eye" onclick="toggleAuthPassword('password-confirm', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
        </div>

        <button type="submit" class="auth-submit">
            <i class="fas fa-check-circle"></i>
            {{ trans('global.reset_password') }}
        </button>

        <div class="auth-bottom">
            <a href="{{ route('login') }}">Back to login</a>
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
