<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', trans('panel.site_title'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

    @yield('styles')

    <style>
        :root {
    --accent: #0855A1;
    --accent-light: #E8F3FA;
    --accent-dark: #06447F;
    --body-bg: #F1F5F9;
    --border: #E2E8F0;
    --muted: #94A3B8;
    --text: #1E293B;
    --card-bg: #FFFFFF;
    --success-bg: #ECFDF5;
    --success-text: #047857;
    --success-border: #A7F3D0;
    --danger-bg: #FEF2F2;
    --danger-text: #B91C1C;
    --danger-border: #FECACA;
}

* {
    box-sizing: border-box;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

html,
body {
    margin: 0;
    padding: 0;
    width: 100%;
    min-width: 100%;
    min-height: 100%;
    background: var(--body-bg);
    color: var(--text);
    overflow-x: hidden;
}

body {
    min-height: 100vh;
}

/* MAIN FULL WIDTH AUTH LAYOUT */
.auth-shell {
    width: 100%;
    min-width: 100%;
    min-height: 100vh;
    display: flex;
    align-items: stretch;
    justify-content: stretch;
    background:
        radial-gradient(circle at top left, rgba(8,85,161,.14), transparent 28%),
        radial-gradient(circle at bottom right, rgba(8,85,161,.08), transparent 30%),
        linear-gradient(135deg, #F8FBFF 0%, #F1F5F9 100%);
}

/* LEFT PANEL */
.auth-left {
    flex: 0 0 46%;
    max-width: 46%;
    min-height: 100vh;
    padding: 48px;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    background: linear-gradient(160deg, #0855A1, #06447F);
    color: #fff;
}

.auth-left::before {
    content: "";
    position: absolute;
    top: -60px;
    right: -60px;
    width: 220px;
    height: 220px;
    border-radius: 999px;
    background: rgba(255,255,255,.08);
}

.auth-left::after {
    content: "";
    position: absolute;
    bottom: -80px;
    left: -80px;
    width: 260px;
    height: 260px;
    border-radius: 999px;
    background: rgba(255,255,255,.06);
}

.auth-left-inner {
    position: relative;
    z-index: 2;
    max-width: 520px;
    width: 100%;
    margin: 0 auto;
}

.auth-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border-radius: 999px;
    background: rgba(255,255,255,.14);
    border: 1px solid rgba(255,255,255,.18);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .02em;
    margin-bottom: 22px;
}

.auth-brand {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 26px;
}

.auth-brand-logo-wrap {
    width: 104px;
    height: 146px;
    padding: 7px;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 14px 32px rgba(0,0,0,.18);
    flex-shrink: 0;
}

.auth-brand-logo {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.auth-brand-icon {
    width: 58px;
    height: 58px;
    border-radius: 18px;
    background: rgba(255,255,255,.16);
    border: 1px solid rgba(255,255,255,.18);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 12px 30px rgba(0,0,0,.12);
    flex-shrink: 0;
}

.auth-brand-icon i {
    font-size: 22px;
    color: #fff;
}

.auth-brand-text h1 {
    margin: 0;
    font-size: 26px;
    font-weight: 800;
    letter-spacing: 0;
}

.auth-brand-text p {
    margin: 4px 0 0;
    color: rgba(255,255,255,.78);
    font-size: 13px;
}

.auth-hero-title {
    font-size: 24px;
    line-height: 1;
    font-weight: 800;
    margin: 0 0 18px;
    letter-spacing: 0;
}

.auth-hero-text {
    margin: 0;
    font-size: 15px;
    line-height: 1.8;
    color: rgba(255,255,255,.82);
    max-width: 470px;
}

.auth-feature-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-top: 34px;
}

.auth-feature-card {
    background: rgba(255,255,255,.11);
    border: 1px solid rgba(255,255,255,.14);
    border-radius: 18px;
    padding: 16px;
    backdrop-filter: blur(6px);
}

.auth-feature-card i {
    font-size: 16px;
    margin-bottom: 10px;
    display: inline-flex;
    width: 34px;
    height: 34px;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: rgba(255,255,255,.14);
}

.auth-feature-card h4 {
    margin: 0 0 6px;
    font-size: 14px;
    font-weight: 700;
}

.auth-feature-card p {
    margin: 0;
    font-size: 12.5px;
    line-height: 1.6;
    color: rgba(255,255,255,.76);
}

/* RIGHT PANEL - FULL AVAILABLE WIDTH */
.auth-right {
    flex: 1;
    min-width: 0;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 36px;
}

.auth-mobile-brand {
    display: none;
}

/* FORM CARD */
.auth-card {
    width: 100%;
    max-width: 560px;
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 24px;
    box-shadow: 0 24px 80px rgba(15,23,42,.10);
    padding: 34px;
}

.auth-card-head {
    margin-bottom: 22px;
}

.auth-card-top {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 7px 12px;
    border-radius: 999px;
    background: var(--accent-light);
    color: var(--accent);
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 16px;
}

.auth-card-head h2 {
    margin: 0;
    font-size: 28px;
    font-weight: 800;
    color: var(--text);
    letter-spacing: -.04em;
}

.auth-card-head p {
    margin: 8px 0 0;
    font-size: 14px;
    color: #64748B;
    line-height: 1.7;
}

.auth-alert {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    border-radius: 14px;
    font-size: 13px;
    margin-bottom: 18px;
}

.auth-alert-success {
    background: var(--success-bg);
    color: var(--success-text);
    border: 1px solid var(--success-border);
}

.auth-alert-error {
    background: var(--danger-bg);
    color: var(--danger-text);
    border: 1px solid var(--danger-border);
}

.auth-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.auth-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.auth-field label {
    display: block;
    margin-bottom: 7px;
    font-size: 13px;
    font-weight: 700;
    color: #334155;
}

.req {
    color: #EF4444;
}

.auth-input-wrap {
    position: relative;
}

.auth-input-wrap > i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 14px;
    color: #94A3B8;
    z-index: 2;
}

.auth-input-wrap input,
.auth-input-wrap select {
    width: 100%;
    min-height: 48px;
    border: 1px solid var(--border);
    background: #F8FAFC;
    color: var(--text);
    border-radius: 14px;
    padding: 12px 14px 12px 42px;
    font-size: 13.5px;
    outline: none;
    transition: all .2s ease;
}

.auth-input-wrap select {
    appearance: none;
    cursor: pointer;
}

.auth-input-wrap.has-eye input {
    padding-right: 48px;
}

.auth-input-wrap input:focus,
.auth-input-wrap select:focus {
    background: #fff;
    border-color: var(--accent);
    box-shadow: 0 0 0 4px rgba(8,85,161,.10);
}

.auth-input-wrap input.error,
.auth-input-wrap select.error {
    border-color: #EF4444;
    background: #fff;
}

.auth-eye {
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
    width: 34px;
    height: 34px;
    border: 0;
    border-radius: 10px;
    background: transparent;
    color: #94A3B8;
    cursor: pointer;
    transition: .2s;
}

.auth-eye:hover {
    background: var(--accent-light);
    color: var(--accent);
}

.auth-error {
    margin: 6px 0 0;
    font-size: 12px;
    color: #DC2626;
}

.auth-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.auth-check {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #64748B;
}

.auth-check input {
    accent-color: var(--accent);
}

.auth-link {
    color: var(--accent);
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
}

.auth-link:hover {
    color: var(--accent-dark);
}

.auth-submit {
    width: 100%;
    min-height: 49px;
    border: 0;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--accent), var(--accent-dark));
    color: #fff;
    font-size: 14px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    cursor: pointer;
    box-shadow: 0 16px 36px rgba(8,85,161,.22);
    transition: all .2s ease;
}

.auth-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 18px 40px rgba(8,85,161,.26);
}

.auth-bottom {
    text-align: center;
    margin-top: 6px;
    font-size: 13px;
    color: #64748B;
}

.auth-bottom a {
    font-weight: 700;
    color: var(--accent);
    text-decoration: none;
}

.auth-bottom a:hover {
    color: var(--accent-dark);
}

/* LAPTOP SMALL */
@media (max-width: 1280px) {
    .auth-left {
        flex-basis: 42%;
        max-width: 42%;
        padding: 40px;
    }

    .auth-hero-title {
        font-size: 18px;
    }

    .auth-feature-grid {
        grid-template-columns: 1fr;
    }

    .auth-right {
        padding: 30px;
    }
}

/* TABLET + MOBILE: LEFT HIDE, RIGHT FULL WIDTH */
@media (max-width: 1100px) {
    .auth-shell {
        display: block;
        width: 100%;
        min-height: 100vh;
    }

    .auth-left {
        display: none !important;
    }

    .auth-right {
        width: 100%;
        min-height: 100vh;
        padding: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }

    .auth-mobile-brand {
        display: block;
        width: 76px;
        height: 118px;
        object-fit: contain;
        margin: 0 auto 18px;
    }

    .auth-card {
        max-width: 600px;
    }
}

/* MOBILE */
@media (max-width: 640px) {
    .auth-right {
        padding: 16px;
        align-items: flex-start;
    }

    .auth-card {
        width: 100%;
        max-width: 100%;
        padding: 24px 18px;
        border-radius: 18px;
        margin: 0;
    }

    .auth-card-head h2 {
        font-size: 24px;
    }

    .auth-grid-2 {
        grid-template-columns: 1fr;
        gap: 16px;
    }
}

/* VERY SMALL MOBILE */
@media (max-width: 380px) {
    .auth-card {
        padding: 20px 14px;
    }

    .auth-input-wrap input,
    .auth-input-wrap select {
        font-size: 13px;
    }
}
    </style>
</head>

<body>
    <div class="auth-shell">
        <div class="auth-left">
            <div class="auth-left-inner">
                <div class="auth-badge">
                    <i class="fas fa-shield-alt"></i>
                    Secure Access Panel
                </div>

                <div class="auth-brand">
                    <div class="auth-brand-logo-wrap">
                        <img src="{{ asset('assets/brand/karmayoga-logo.png') }}"
                             alt="Karmayoga Academy"
                             class="auth-brand-logo">
                    </div>

                    <div class="auth-brand-text">
                        <h1>{{ trans('panel.site_title') }}</h1>
                        <p>Smart institute management system</p>
                    </div>
                </div>

                <h2 class="auth-hero-title">Manage admissions, academics, fees and communication in one place.</h2>

                <p class="auth-hero-text">
                    A modern and structured dashboard for institutes to manage branches, students, teachers,
                    fee collections, exams, study materials, notices and more with better control and visibility.
                </p>

                <div class="auth-feature-grid">
                    <div class="auth-feature-card">
                        <i class="fas fa-user-graduate"></i>
                        <h4>Student Management</h4>
                        <p>Admissions, profiles, batch mapping and academic visibility.</p>
                    </div>

                    <div class="auth-feature-card">
                        <i class="fas fa-rupee-sign"></i>
                        <h4>Finance Tracking</h4>
                        <p>Fee payments, receipts, salary records and expense monitoring.</p>
                    </div>

                    <div class="auth-feature-card">
                        <i class="fas fa-book-reader"></i>
                        <h4>Academic Control</h4>
                        <p>Timetables, exams, study materials and classroom workflow.</p>
                    </div>

                    <div class="auth-feature-card">
                        <i class="fab fa-whatsapp"></i>
                        <h4>Parent Communication</h4>
                        <p>Alerts, notices and status notifications with better coordination.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="auth-right">
            <img src="{{ asset('assets/brand/karmayoga-logo.png') }}"
                 alt="Karmayoga Academy"
                 class="auth-mobile-brand">
            @yield('content')
        </div>
    </div>

    @yield('scripts')
</body>

</html>
