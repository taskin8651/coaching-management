<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ trans('panel.site_title') }}</title>

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Alpine.js --}}
    <script src="//unpkg.com/alpinejs" defer></script>

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- Datatables --}}
    <link rel="stylesheet" href="//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="//cdn.datatables.net/buttons/1.2.4/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="//cdn.datatables.net/select/1.3.0/css/select.dataTables.min.css">

    {{-- Select2 / Dropzone --}}
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/min/dropzone.min.css">

    {{-- Admin CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/admin/css/admin.css') }}?v={{ filemtime(public_path('assets/admin/css/admin.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/admin-form.css') }}?v={{ filemtime(public_path('assets/admin/css/admin-form.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/admin-list.css') }}?v={{ filemtime(public_path('assets/admin/css/admin-list.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/admin-show.css') }}?v={{ filemtime(public_path('assets/admin/css/admin-show.css')) }}">

    @yield('styles')
</head>

<body>

<div class="admin-layout">

    {{-- Sidebar menu --}}
    @include('partials.menu')

    <div id="sidebar-overlay" onclick="closeSidebar()"></div>

    <div id="main-content" class="admin-main">

        {{-- Header --}}
        <header id="main-header">

            <div class="header-left">

                {{-- Mobile hamburger --}}
                <button type="button"
                        class="header-btn header-btn-sm lg:hidden"
                        onclick="toggleMobileSidebar()">
                    <i class="fas fa-bars"></i>
                </button>

                {{-- Desktop collapse --}}
                <button type="button"
                        class="header-btn header-btn-sm hidden lg:flex"
                        id="sidebar-toggle"
                        onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>

                {{-- Breadcrumb --}}
                <div class="breadcrumb-wrap hidden sm:flex">
                    <span class="breadcrumb-item">{{ trans('panel.site_title') }}</span>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-item active">@yield('page-title', 'Dashboard')</span>
                </div>

            </div>

            <div class="header-right">

                {{-- Search --}}
                <div class="admin-search hidden md:flex">
                    <input type="text" placeholder="Search...">
                    <i class="fas fa-search"></i>
                </div>

                {{-- Language --}}
                @if(count(config('panel.available_languages', [])) > 1)
                    <div x-data="{ open:false }" class="relative">
                        <button type="button" @click="open = !open" class="lang-btn">
                            {{ app()->getLocale() }}
                            <i class="fas fa-chevron-down"></i>
                        </button>

                        <div x-show="open"
                             x-transition
                             @click.outside="open=false"
                             class="lang-menu">
                            @foreach(config('panel.available_languages') as $langLocale => $langName)
                                <a href="{{ url()->current() }}?change_language={{ $langLocale }}">
                                    {{ strtoupper($langLocale) }} – {{ $langName }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Notifications --}}
                <button type="button" class="header-btn notification-btn">
                    <i class="fas fa-bell"></i>
                    <span class="notif-dot"></span>
                </button>

                {{-- User dropdown --}}
                <div x-data="{ open:false }" class="relative">

                    <button type="button"
                            @click="open = !open"
                            class="user-dropdown-btn">

                        <div class="user-dropdown-avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>

                        <div class="hidden sm:block text-start">
                            <p class="user-dropdown-name">{{ auth()->user()->name }}</p>
                            <p class="user-dropdown-role">
                                {{ auth()->user()->roles->pluck('title')->join(', ') ?: 'User' }}
                            </p>
                        </div>

                        <i class="fas fa-chevron-down hidden sm:block user-dropdown-chevron"></i>
                    </button>

                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         @click.outside="open=false"
                         class="user-menu">

                        <div class="user-menu-head">
                            <p class="user-menu-head-name">{{ auth()->user()->name }}</p>
                            <p class="user-menu-head-email">{{ auth()->user()->email }}</p>
                        </div>

                        <div class="user-menu-body">

                            @if(file_exists(app_path('Http/Controllers/Auth/ChangePasswordController.php')))
                                <a href="{{ route('profile.password.edit') }}" class="user-menu-link">
                                    <i class="fas fa-key"></i>
                                    Change Password
                                </a>
                            @endif

                            <div class="user-menu-divider"></div>

                            <a href="#"
                               onclick="event.preventDefault(); document.getElementById('logoutform').submit();"
                               class="user-menu-link danger">
                                <i class="fas fa-sign-out-alt"></i>
                                Sign Out
                            </a>

                        </div>
                    </div>

                </div>

            </div>

        </header>

        {{-- Content --}}
        <main class="admin-content">

            @if(session('message'))
                <div class="alert-success">
                    <i class="fas fa-check-circle"></i>
                    {{ session('message') }}
                </div>
            @endif

            @if($errors->count() > 0)
                <div class="alert-error">
                    <div class="alert-error-title">
                        <i class="fas fa-exclamation-circle"></i>
                        <strong>Please fix the following errors:</strong>
                    </div>

                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')

        </main>

    </div>

</div>

{{-- Logout Form --}}
<form id="logoutform" action="{{ route('logout') }}" method="POST" style="display:none;">
    @csrf
</form>

@if(session('popup_error'))
    <style>
        .premium-popup-overlay{position:fixed;inset:0;background:rgba(13,25,48,.55);backdrop-filter:blur(3px);display:flex;align-items:center;justify-content:center;z-index:99999;opacity:0;pointer-events:none;transition:opacity .25s ease;padding:16px}
        .premium-popup-overlay.show{opacity:1;pointer-events:auto}
        .premium-popup-box{background:#fff;border-radius:18px;max-width:420px;width:100%;padding:32px 28px 26px;text-align:center;box-shadow:0 30px 70px rgba(15,35,68,.35);transform:scale(.9) translateY(10px);opacity:0;transition:transform .25s ease,opacity .25s ease}
        .premium-popup-overlay.show .premium-popup-box{transform:scale(1) translateY(0);opacity:1}
        .premium-popup-icon{width:64px;height:64px;border-radius:50%;margin:0 auto 18px;display:grid;place-items:center;background:linear-gradient(135deg,#ff9d3d,#ef4444);color:#fff;font-size:28px;box-shadow:0 12px 28px rgba(239,68,68,.35)}
        .premium-popup-title{font-size:18px;font-weight:800;color:#0d1f3c;margin:0 0 8px}
        .premium-popup-message{font-size:14px;color:#5b6b82;line-height:1.6;margin:0 0 22px}
        .premium-popup-btn{border:0;background:linear-gradient(135deg,#2f80ff,#4866ff);color:#fff;font-weight:700;font-size:14px;padding:11px 30px;border-radius:10px;cursor:pointer;box-shadow:0 12px 24px rgba(47,128,255,.28)}
        .premium-popup-btn:hover{opacity:.92}
    </style>
    <div class="premium-popup-overlay" id="premiumPopupOverlay">
        <div class="premium-popup-box">
            <div class="premium-popup-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <p class="premium-popup-title">Action Not Allowed</p>
            <p class="premium-popup-message">{{ session('popup_error') }}</p>
            <button type="button" class="premium-popup-btn" onclick="document.getElementById('premiumPopupOverlay').classList.remove('show')">Got it</button>
        </div>
    </div>
    <script>
        document.getElementById('premiumPopupOverlay').classList.add('show');
    </script>
@endif

{{-- JS --}}
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/buttons/1.2.4/js/dataTables.buttons.min.js"></script>
<script src="//cdn.datatables.net/buttons/1.2.4/js/buttons.html5.min.js"></script>
<script src="//cdn.datatables.net/buttons/1.2.4/js/buttons.print.min.js"></script>
<script src="//cdn.datatables.net/select/1.3.0/js/dataTables.select.min.js"></script>
<script src="//cdn.ckeditor.com/ckeditor5/16.0.0/classic/ckeditor.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/dropzone/5.5.1/min/dropzone.min.js"></script>

{{-- Admin JS --}}
<script src="{{ asset('assets/admin/js/admin.js') }}?v={{ filemtime(public_path('assets/admin/js/admin.js')) }}"></script>
<script src="{{ asset('assets/admin/js/admin-form.js') }}?v={{ filemtime(public_path('assets/admin/js/admin-form.js')) }}"></script>
<script src="{{ asset('assets/admin/js/admin-list.js') }}?v={{ filemtime(public_path('assets/admin/js/admin-list.js')) }}"></script>

@yield('scripts')

</body>
</html>
