@extends('layouts.app')

@section('title', __('Confirm Password') . ' | ' . trans('panel.site_title'))

@section('content')
<div class="auth-card">
    <div class="auth-card-head">
        <div class="auth-card-top">
            <i class="fas fa-user-shield"></i>
            Security Check
        </div>

        <h2>{{ __('Confirm Password') }}</h2>
        <p>{{ __('Please confirm your password before continuing.') }}</p>
    </div>

    @if($errors->any())
        <div class="auth-alert auth-alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span>Please enter your current password correctly.</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.confirm') }}" class="auth-form">
        @csrf

        <div class="auth-field">
            <label for="password">{{ __('Password') }} <span class="req">*</span></label>

            <div class="auth-input-wrap has-eye">
                <i class="fas fa-lock"></i>

                <input id="password"
                       type="password"
                       name="password"
                       required
                       autocomplete="current-password"
                       placeholder="Enter your password"
                       class="{{ $errors->has('password') ? 'error' : '' }}">

                <button type="button" class="auth-eye" onclick="toggleAuthPassword('password', this)">
                    <i class="fas fa-eye"></i>
                </button>
            </div>

            @error('password')
                <p class="auth-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="auth-submit">
            <i class="fas fa-check"></i>
            {{ __('Confirm Password') }}
        </button>

        @if(Route::has('password.request'))
            <div class="auth-bottom">
                <a href="{{ route('password.request') }}">{{ __('Forgot Your Password?') }}</a>
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
