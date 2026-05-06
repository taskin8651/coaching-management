<aside id="sidebar">

    {{-- BRAND --}}
    <div class="sidebar-brand">
        <div class="brand-area">
            <div class="brand-icon">
                <i class="fas fa-bolt"></i>
            </div>

            <span class="brand-text">
                {{ trans('panel.site_title') }}
            </span>
        </div>
    </div>

    {{-- USER MINI CARD --}}
    <div class="user-info">
        <div class="user-avatar">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>

        <div class="user-meta">
            <p class="user-name">{{ auth()->user()->name }}</p>
            <p class="user-role">Administrator</p>
        </div>
    </div>

    {{-- NAV --}}
    <nav class="sidebar-nav">

        <p class="sidebar-section-title nav-label">Main</p>

        {{-- Dashboard --}}
        <a href="{{ route('admin.home') }}"
           data-tooltip="Dashboard"
           class="nav-link {{ request()->routeIs('admin.home') ? 'active' : '' }}">
            <i class="fas fa-chart-pie nav-icon"></i>
            <span class="nav-label">{{ trans('global.dashboard') }}</span>
        </a>

        {{-- USER MANAGEMENT GROUP --}}
        @can('user_management_access')
            @php
                $umActive = request()->is('admin/permissions*')
                    || request()->is('admin/roles*')
                    || request()->is('admin/users*')
                    || request()->is('admin/audit-logs*');
            @endphp

            <div x-data="{ open: {{ $umActive ? 'true' : 'false' }} }">

                <button type="button"
                        @click="open = !open"
                        data-tooltip="Users"
                        class="nav-link nav-group-btn {{ $umActive ? 'active' : '' }}">

                    <div class="nav-group-left">
                        <i class="fas fa-users nav-icon"></i>
                        <span class="nav-label">{{ trans('cruds.userManagement.title') }}</span>
                    </div>

                    <i class="fas fa-chevron-right chevron"
                       :style="open ? 'transform:rotate(90deg)' : ''"></i>
                </button>

                <div class="submenu"
                     x-show="open"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-1">

                    @can('permission_access')
                        <a href="{{ route('admin.permissions.index') }}"
                           class="sub-link {{ request()->is('admin/permissions*') ? 'active' : '' }}">
                            <i class="fas fa-key"></i>
                            {{ trans('cruds.permission.title') }}
                        </a>
                    @endcan

                    @can('role_access')
                        <a href="{{ route('admin.roles.index') }}"
                           class="sub-link {{ request()->is('admin/roles*') ? 'active' : '' }}">
                            <i class="fas fa-shield-alt"></i>
                            {{ trans('cruds.role.title') }}
                        </a>
                    @endcan

                    @can('user_access')
                        <a href="{{ route('admin.users.index') }}"
                           class="sub-link {{ request()->is('admin/users*') ? 'active' : '' }}">
                            <i class="fas fa-user-circle"></i>
                            {{ trans('cruds.user.title') }}
                        </a>
                    @endcan

                    @can('audit_log_access')
                        <a href="{{ route('admin.audit-logs.index') }}"
                           class="sub-link {{ request()->is('admin/audit-logs*') ? 'active' : '' }}">
                            <i class="fas fa-history"></i>
                            {{ trans('cruds.auditLog.title') }}
                        </a>
                    @endcan

                </div>
            </div>
        @endcan

        {{-- COACHING MANAGEMENT GROUP --}}
@can('branch_access')
    @php
        $coachingActive = request()->is('admin/branches*')
            || request()->is('admin/courses*')
            || request()->is('admin/subjects*')
            || request()->is('admin/batches*')
            || request()->is('admin/teachers*')
            || request()->is('admin/staff*')
            || request()->is('admin/students*')
        || request()->is('admin/enquiries*')
        || request()->is('admin/fee-payments*')
        || request()->is('admin/expenses*')
|| request()->is('admin/salary-payments*')
|| request()->is('admin/exams*')
|| request()->is('admin/study-materials*')
|| request()->is('admin/notices*')
            ;

    @endphp


    <div x-data="{ open: {{ $coachingActive ? 'true' : 'false' }} }">

        <button type="button"
                @click="open = !open"
                data-tooltip="Coaching"
                class="nav-link nav-group-btn {{ $coachingActive ? 'active' : '' }}">

            <div class="nav-group-left">
                <i class="fas fa-school nav-icon"></i>
                <span class="nav-label">Coaching Management</span>
            </div>

            <i class="fas fa-chevron-right chevron"
               :style="open ? 'transform:rotate(90deg)' : ''"></i>
        </button>

        <div class="submenu"
             x-show="open"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1">

            @can('branch_access')
                <a href="{{ route('admin.branches.index') }}"
                   class="sub-link {{ request()->is('admin/branches*') ? 'active' : '' }}">
                    <i class="fas fa-code-branch"></i>
                    Branches
                </a>
            @endcan

            @can('course_access')
    <a href="{{ route('admin.courses.index') }}"
       class="sub-link {{ request()->is('admin/courses*') ? 'active' : '' }}">
        <i class="fas fa-book"></i>
        Courses
    </a>
@endcan

@can('subject_access')
    <a href="{{ route('admin.subjects.index') }}"
       class="sub-link {{ request()->is('admin/subjects*') ? 'active' : '' }}">
        <i class="fas fa-book-open"></i>
        Subjects
    </a>
@endcan

@can('batch_access')
    <a href="{{ route('admin.batches.index') }}"
       class="sub-link {{ request()->is('admin/batches*') ? 'active' : '' }}">
        <i class="fas fa-layer-group"></i>
        Batches
    </a>
@endcan

@can('teacher_access')
    <a href="{{ route('admin.teachers.index') }}"
       class="sub-link {{ request()->is('admin/teachers*') ? 'active' : '' }}">
        <i class="fas fa-chalkboard-teacher"></i>
        Teachers
    </a>
@endcan

@can('staff_access')
    <a href="{{ route('admin.staff.index') }}"
       class="sub-link {{ request()->is('admin/staff*') ? 'active' : '' }}">
        <i class="fas fa-user-friends"></i>
        Staff
    </a>
@endcan

@can('student_access')
    <a href="{{ route('admin.students.index') }}"
       class="sub-link {{ request()->is('admin/students*') ? 'active' : '' }}">
        <i class="fas fa-user-graduate"></i>
        Students
    </a>
@endcan
@can('enquiry_access')
    <a href="{{ route('admin.enquiries.index') }}"
       class="sub-link {{ request()->is('admin/enquiries*') ? 'active' : '' }}">
        <i class="fas fa-headset"></i>
        Enquiries
    </a>
@endcan
@can('fee_payment_access')
    <a href="{{ route('admin.fee-payments.index') }}"
       class="sub-link {{ request()->is('admin/fee-payments*') ? 'active' : '' }}">
        <i class="fas fa-rupee-sign"></i>
        Fee Payments
    </a>
@endcan
@can('expense_access')
    <a href="{{ route('admin.expenses.index') }}"
         class="sub-link {{ request()->is('admin/expenses*') ? 'active' : '' }}">
        <i class="fas fa-money-bill-wave"></i>
        Expenses
    </a>
@endcan
@can('salary_payment_access')
    <a href="{{ route('admin.salary-payments.index') }}"
       class="sub-link {{ request()->is('admin/salary-payments*') ? '   active' : '' }}">
        <i class="fas fa-hand-holding-usd"></i>
        Salary Payments
    </a>    
    @endcan

    @can('exam_access')
    <a href="{{ route('admin.exams.index') }}"
       class="sub-link {{ request()->is('admin/exams*') ? 'active' : '' }}">
        <i class="fas fa-clipboard-list"></i>
        Exams / Tests
    </a>
@endcan

@can('study_material_access')
    <a href="{{ route('admin.study-materials.index') }}"
       class="sub-link {{ request()->is('admin/study-materials*') ? 'active' : '' }}">
        <i class="fas fa-book-reader"></i>
        Study Materials
    </a>
@endcan

@can('notice_access')
    <a href="{{ route('admin.notices.index') }}"
       class="sub-link {{ request()->is('admin/notices*') ? 'active' : '' }}">
        <i class="fas fa-bullhorn"></i>
        Notices
    </a>
@endcan
        </div>
    </div>
@endcan

        <div class="nav-divider"></div>

        <p class="sidebar-section-title compact nav-label">Account</p>

        {{-- Change Password --}}
        @if(file_exists(app_path('Http/Controllers/Auth/ChangePasswordController.php')))
            @can('profile_password_edit')
                <a href="{{ route('profile.password.edit') }}"
                   data-tooltip="Password"
                   class="nav-link {{ request()->is('profile/password*') ? 'active' : '' }}">
                    <i class="fas fa-key nav-icon"></i>
                    <span class="nav-label">{{ trans('global.change_password') }}</span>
                </a>
            @endcan
        @endif

        {{-- Settings --}}
        <a href="#"
           data-tooltip="Settings"
           class="nav-link">
            <i class="fas fa-cog nav-icon"></i>
            <span class="nav-label">Settings</span>
        </a>

    </nav>

    {{-- LOGOUT --}}
    <div class="sidebar-footer">
        <a href="#"
           onclick="event.preventDefault(); document.getElementById('logoutform').submit();"
           data-tooltip="Logout"
           class="nav-link logout-link">
            <i class="fas fa-sign-out-alt nav-icon"></i>
            <span class="nav-label">{{ trans('global.logout') }}</span>
        </a>
    </div>

</aside>