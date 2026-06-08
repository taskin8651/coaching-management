@extends('layouts.app')

@section('title', trans('global.login') . ' | ' . trans('panel.site_title'))

@section('content')
<div class="auth-card">

    <div class="auth-card-head">
        <div class="auth-card-top">
            <i class="fas fa-lock"></i>
            Secure Login
        </div>

        <h2>{{ trans('global.login') }}</h2>
        <p>Enter your credentials to access your dashboard panel.</p>
    </div>

    @if(session('message'))
        <div class="auth-alert auth-alert-success">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="auth-alert auth-alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span>Please check your login details and try again.</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf

        <div class="auth-field">
            <label for="email">{{ trans('global.login_email') }} <span class="req">*</span></label>

            <div class="auth-input-wrap">
                <i class="fas fa-envelope"></i>

                <input type="email"
                       name="email"
                       id="email"
                       value="{{ old('email') }}"
                       required
                       autofocus
                       placeholder="Enter your email address"
                       class="{{ $errors->has('email') ? 'error' : '' }}">
            </div>

            @if($errors->has('email'))
                <p class="auth-error">{{ $errors->first('email') }}</p>
            @endif
        </div>

        <div class="auth-field">
            <label for="password">{{ trans('global.login_password') }} <span class="req">*</span></label>

            <div class="auth-input-wrap has-eye">
                <i class="fas fa-lock"></i>

                <input type="password"
                       name="password"
                       id="password"
                       required
                       placeholder="Enter your password"
                       class="{{ $errors->has('password') ? 'error' : '' }}">

                <button type="button" class="auth-eye" onclick="toggleAuthPassword('password', this)">
                    <i class="fas fa-eye"></i>
                </button>
            </div>

            @if($errors->has('password'))
                <p class="auth-error">{{ $errors->first('password') }}</p>
            @endif
        </div>

        <div class="auth-row">
            <label class="auth-check">
                <input type="checkbox" name="remember">
                <span>{{ trans('global.remember_me') }}</span>
            </label>

            @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="auth-link">
                    {{ trans('global.forgot_password') }}
                </a>
            @endif
        </div>

        <button type="submit" class="auth-submit">
            <i class="fas fa-sign-in-alt"></i>
            {{ trans('global.login') }}
        </button>

        @if(Route::has('register'))
            <div class="auth-bottom">
                Don’t have an account?
                <a href="{{ route('register') }}">Create one</a>
            </div>
        @endif
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