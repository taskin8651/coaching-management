@extends('layouts.app')

@section('title', __('Verify Your Email Address') . ' | ' . trans('panel.site_title'))

@section('content')
<div class="auth-card">
    <div class="auth-card-head">
        <div class="auth-card-top">
            <i class="fas fa-envelope-open-text"></i>
            Email Verification
        </div>

        <h2>{{ __('Verify Your Email Address') }}</h2>
        <p>{{ __('Before proceeding, please check your email for a verification link.') }}</p>
    </div>

    @if (session('resent'))
        <div class="auth-alert auth-alert-success">
            <i class="fas fa-check-circle"></i>
            <span>{{ __('A fresh verification link has been sent to your email address.') }}</span>
        </div>
    @endif

    <div class="auth-alert auth-alert-success">
        <i class="fas fa-info-circle"></i>
        <span>{{ __('If you did not receive the email, you can request another verification link.') }}</span>
    </div>

    <form method="POST" action="{{ route('verification.resend') }}" class="auth-form">
        @csrf

        <button type="submit" class="auth-submit">
            <i class="fas fa-sync-alt"></i>
            {{ __('Resend Verification Link') }}
        </button>

        <div class="auth-bottom">
            <a href="{{ route('login') }}">{{ trans('global.login') }}</a>
        </div>
    </form>
</div>
@endsection
