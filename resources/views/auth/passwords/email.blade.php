@extends('layouts.app')

@section('title', trans('global.reset_password') . ' | ' . trans('panel.site_title'))

@section('content')
<div class="auth-card">
    <div class="auth-card-head">
        <div class="auth-card-top">
            <i class="fas fa-paper-plane"></i>
            Password Help
        </div>

        <h2>{{ trans('global.reset_password') }}</h2>
        <p>Enter your registered email address. We will send you a secure password reset link.</p>
    </div>

    @if(session('status'))
        <div class="auth-alert auth-alert-success">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="auth-alert auth-alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span>Please enter a valid registered email address.</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="auth-form">
        @csrf

        <div class="auth-field">
            <label for="email">{{ trans('global.login_email') }} <span class="req">*</span></label>

            <div class="auth-input-wrap">
                <i class="fas fa-envelope"></i>

                <input id="email"
                       type="email"
                       name="email"
                       value="{{ old('email') }}"
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

        <button type="submit" class="auth-submit">
            <i class="fas fa-paper-plane"></i>
            {{ trans('global.send_password') }}
        </button>

        <div class="auth-bottom">
            Remember your password?
            <a href="{{ route('login') }}">Back to login</a>
        </div>
    </form>
</div>
@endsection
