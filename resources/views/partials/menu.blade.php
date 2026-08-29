<aside id="sidebar">

    {{-- BRAND --}}
    <div class="sidebar-brand">
        <div class="brand-area">
            <div class="brand-logo-shell">
                <img src="{{ asset('assets/brand/karmayoga-logo.png') }}"
                     alt="Karmayoga Academy"
                     class="brand-logo">
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

            <p class="user-role">
                {{ auth()->user()->roles->pluck('title')->first() ?? 'User' }}
            </p>
        </div>
    </div>

    {{-- NAV --}}
    <nav class="sidebar-nav">

        <p class="sidebar-section-title nav-label">Main</p>

        {{-- Dashboard --}}
        @can('dashboard_access')
            <a href="{{ route('admin.home') }}"
               data-tooltip="Dashboard"
               class="nav-link {{ request()->routeIs('admin.home') ? 'active' : '' }}">
                <i class="fas fa-chart-pie nav-icon"></i>
                <span class="nav-label">{{ trans('global.dashboard') }}</span>
            </a>
        @endcan

        @can('my_portal_access')
            <a href="{{ route('admin.my-portal.index') }}"
               data-tooltip="My Portal"
               class="nav-link {{ request()->routeIs('admin.my-portal.*') ? 'active' : '' }}">
                <i class="fas fa-id-card nav-icon"></i>
                <span class="nav-label">My Portal</span>
            </a>
        @endcan

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
                        <span class="nav-label">User Management</span>
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

        {{-- SETUP GROUP --}}
        @canany(['branch_access', 'course_access', 'subject_access', 'batch_access'])
            @php
                $setupActive = request()->is('admin/branches*')
                    || request()->is('admin/courses*')
                    || request()->is('admin/subjects*')
                    || request()->is('admin/batches*');
            @endphp

            <div x-data="{ open: {{ $setupActive ? 'true' : 'false' }} }">
                <button type="button"
                        @click="open = !open"
                        data-tooltip="Setup"
                        class="nav-link nav-group-btn {{ $setupActive ? 'active' : '' }}">

                    <div class="nav-group-left">
                        <i class="fas fa-sliders-h nav-icon"></i>
                        <span class="nav-label">Setup</span>
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

                </div>
            </div>
        @endcanany

        {{-- PEOPLE GROUP --}}
        @canany(['teacher_access', 'staff_access', 'student_access'])
            @php
                $peopleActive = request()->is('admin/teachers*')
                    || request()->is('admin/staff*')
                    || request()->is('admin/students*');
            @endphp

            <div x-data="{ open: {{ $peopleActive ? 'true' : 'false' }} }">
                <button type="button"
                        @click="open = !open"
                        data-tooltip="People"
                        class="nav-link nav-group-btn {{ $peopleActive ? 'active' : '' }}">

                    <div class="nav-group-left">
                        <i class="fas fa-user-friends nav-icon"></i>
                        <span class="nav-label">People</span>
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
                            <i class="fas fa-user-tie"></i>
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

                </div>
            </div>
        @endcanany

        {{-- ENQUIRIES --}}
        @can('enquiry_access')
            <a href="{{ route('admin.enquiries.index') }}"
               data-tooltip="Enquiries"
               class="nav-link {{ request()->is('admin/enquiries*') ? 'active' : '' }}">
                <i class="fas fa-headset nav-icon"></i>
                <span class="nav-label">Enquiries</span>
            </a>
        @endcan

        {{-- FINANCE GROUP --}}
        @canany(['fee_payment_access', 'fee_installment_access', 'expense_access', 'salary_payment_access', 'salary_report_access', 'fee_master_access', 'fee_account_access', 'concession_access', 'student_fee_ledger_access', 'refund_access', 'credit_access'])
            @php
                $financeActive = request()->is('admin/fee-payments*')
                    || request()->is('admin/expenses*')
                    || request()->is('admin/fee-structures*')
                    || request()->is('admin/salary-payments*')
                    || request()->is('admin/fee-heads*')
                    || request()->is('admin/fee-accounts*')
                    || request()->is('admin/concessions*')
                    || request()->is('admin/student-fee-ledgers*')
                    || request()->is('admin/refunds*')
                    || request()->is('admin/student-credits*');
            @endphp

            <div x-data="{ open: {{ $financeActive ? 'true' : 'false' }} }">
                <button type="button"
                        @click="open = !open"
                        data-tooltip="Finance"
                        class="nav-link nav-group-btn {{ $financeActive ? 'active' : '' }}">

                    <div class="nav-group-left">
                        <i class="fas fa-wallet nav-icon"></i>
                        <span class="nav-label">Finance</span>
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
                     @can('fee_master_access')
    <a href="{{ route('admin.fee-heads.index') }}"
       class="sub-link {{ request()->is('admin/fee-heads*') ? 'active' : '' }}">
        <i class="fas fa-tags"></i>
        Fee Master
    </a>
@endcan

                    @can('fee_account_access')
    <a href="{{ route('admin.fee-accounts.index') }}"
       class="sub-link {{ request()->is('admin/fee-accounts*') ? 'active' : '' }}">
        <i class="fas fa-university"></i>
        Fee Accounts
    </a>
@endcan

                     @can('fee_structure_access')
    <a href="{{ route('admin.fee-structures.index') }}"
       class="sub-link {{ request()->is('admin/fee-structures*') ? 'active' : '' }}">
        <i class="fas fa-list-alt"></i>
        Fee Structures
    </a>
@endcan

                    @can('student_fee_ledger_access')
    <a href="{{ route('admin.student-fee-ledgers.index') }}"
       class="sub-link {{ request()->is('admin/student-fee-ledgers*') ? 'active' : '' }}">
        <i class="fas fa-file-invoice-dollar"></i>
        Student Fee Ledgers
    </a>
@endcan

                    @can('refund_access')
    <a href="{{ route('admin.refunds.index') }}"
       class="sub-link {{ request()->is('admin/refunds*') ? 'active' : '' }}">
        <i class="fas fa-undo-alt"></i>
        Refunds
    </a>
@endcan

                    @can('credit_access')
    <a href="{{ route('admin.student-credits.index') }}"
       class="sub-link {{ request()->is('admin/student-credits*') ? 'active' : '' }}">
        <i class="fas fa-coins"></i>
        Student Credits
    </a>
@endcan

                    @can('concession_access')
    <a href="{{ route('admin.concessions.index') }}"
       class="sub-link {{ request()->is('admin/concessions*') ? 'active' : '' }}">
        <i class="fas fa-percent"></i>
        Concessions
    </a>
@endcan

                    @can('fee_payment_access')
                        <a href="{{ route('admin.fee-payments.index') }}"
                           class="sub-link {{ request()->is('admin/fee-payments*') ? 'active' : '' }}">
                            <i class="fas fa-rupee-sign"></i>
                            Fee Payments
                        </a>
                    @endcan

                    @can('fee_installment_access')
                        <a href="{{ route('admin.fee-installments.index') }}"
                           class="sub-link {{ request()->is('admin/fee-installments*') ? 'active' : '' }}">
                            <i class="fas fa-calendar-alt"></i>
                            Fee Installments
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
                           class="sub-link {{ request()->is('admin/salary-payments*') ? 'active' : '' }}">
                            <i class="fas fa-hand-holding-usd"></i>
                            Salary Payments
                        </a>
                    @endcan

                    @can('salary_report_access')
                        <a href="{{ route('admin.salary-reports.index') }}"
                           class="sub-link {{ request()->is('admin/salary-reports*') ? 'active' : '' }}">
                            <i class="fas fa-chart-line"></i>
                            Salary Reports
                        </a>
                    @endcan

                </div>
            </div>
        @endcanany

        {{-- EVENTS GROUP --}}
        @canany(['event_access', 'external_contact_access'])
            @php
                $eventsActive = request()->is('admin/events*')
                    || request()->is('admin/external-contacts*');
            @endphp

            <div x-data="{ open: {{ $eventsActive ? 'true' : 'false' }} }">
                <button type="button"
                        @click="open = !open"
                        data-tooltip="Events"
                        class="nav-link nav-group-btn {{ $eventsActive ? 'active' : '' }}">

                    <div class="nav-group-left">
                        <i class="fas fa-calendar-alt nav-icon"></i>
                        <span class="nav-label">Events</span>
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
                    @can('event_access')
                        <a href="{{ route('admin.events.index') }}"
                           class="sub-link {{ request()->is('admin/events*') ? 'active' : '' }}">
                            <i class="fas fa-ticket-alt"></i>
                            Events
                        </a>
                    @endcan

                    @can('external_contact_access')
                        <a href="{{ route('admin.external-contacts.index') }}"
                           class="sub-link {{ request()->is('admin/external-contacts*') ? 'active' : '' }}">
                            <i class="fas fa-address-book"></i>
                            External Contacts
                        </a>
                    @endcan
                </div>
            </div>
        @endcanany

        {{-- ACADEMIC GROUP --}}
        @canany(['exam_access', 'exam_type_access', 'report_card_access', 'study_material_access', 'student_batch_access', 'student_attendance_access', 'teacher_attendance_access', 'staff_attendance_access', 'staff_timetable_access', 'faculty_log_access', 'extra_class_access', 'timetable_access', 'timetable_substitute', 'homework_access', 'student_remark_access', 'holiday_access'])
            @php
                $academicActive = request()->is('admin/exams*')
                    || request()->is('admin/exam-types*')
                    || request()->is('admin/study-materials*')
                    || request()->is('admin/student-batches*')
                    || request()->is('admin/student-attendances*')
                    || request()->is('admin/teacher-attendances*')
                    || request()->is('admin/staff-attendances*')
                    || request()->is('admin/staff-timetables*')
                    || request()->is('admin/faculty-log-books*')
                    || request()->is('admin/extra-classes*')
                    || request()->is('admin/timetables*')
                    || request()->is('admin/timetable-substitutions*')
                    || request()->is('admin/holidays*');
            @endphp

            <div x-data="{ open: {{ $academicActive ? 'true' : 'false' }} }">
                <button type="button"
                        @click="open = !open"
                        data-tooltip="Academic"
                        class="nav-link nav-group-btn {{ $academicActive ? 'active' : '' }}">

                    <div class="nav-group-left">
                        <i class="fas fa-graduation-cap nav-icon"></i>
                        <span class="nav-label">Academic</span>
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

                    @can('exam_access')
                        <a href="{{ route('admin.exams.index') }}"
                           class="sub-link {{ request()->is('admin/exams*') ? 'active' : '' }}">
                            <i class="fas fa-clipboard-list"></i>
                            Exams / Tests
                        </a>
                    @endcan

                    @can('exam_type_access')
                        <a href="{{ route('admin.exam-types.index') }}"
                           class="sub-link {{ request()->is('admin/exam-types*') ? 'active' : '' }}">
                            <i class="fas fa-list-alt"></i>
                            Exam Types
                        </a>
                    @endcan

                    @can('report_card_access')
                        <a href="{{ route('admin.report-cards.index') }}"
                           class="sub-link {{ request()->is('admin/report-cards*') ? 'active' : '' }}">
                            <i class="fas fa-award"></i>
                            Report Cards
                        </a>
                    @endcan

                    @can('timetable_access')
                        <a href="{{ route('admin.timetables.index') }}"
                           class="sub-link {{ request()->is('admin/timetables*') ? 'active' : '' }}">
                            <i class="fas fa-calendar"></i>
                            Timetable
                        </a>
                    @endcan

                    @can('timetable_substitute')
                        <a href="{{ route('admin.timetable-substitutions.index') }}"
                           class="sub-link {{ request()->is('admin/timetable-substitutions*') ? 'active' : '' }}">
                            <i class="fas fa-user-clock"></i>
                            Substitute Teachers
                        </a>
                    @endcan

                    @can('homework_access')
                        <a href="{{ route('admin.homeworks.index') }}"
                           class="sub-link {{ request()->is('admin/homeworks*') ? 'active' : '' }}">
                            <i class="fas fa-tasks"></i>
                            Homework
                        </a>
                    @endcan

                    @can('student_remark_access')
                        <a href="{{ route('admin.student-remarks.index') }}"
                           class="sub-link {{ request()->is('admin/student-remarks*') ? 'active' : '' }}">
                            <i class="fas fa-comment-alt"></i>
                            Remark Log
                        </a>
                    @endcan

                    @can('study_material_access')
                        <a href="{{ route('admin.study-materials.index') }}"
                           class="sub-link {{ request()->is('admin/study-materials*') ? 'active' : '' }}">
                            <i class="fas fa-book-reader"></i>
                            Study Materials
                        </a>
                    @endcan

                    @can('student_batch_access')
                        <a href="{{ route('admin.student-batches.index') }}"
                           class="sub-link {{ request()->is('admin/student-batches*') ? 'active' : '' }}">
                            <i class="fas fa-random"></i>
                            Student Batches
                        </a>
                    @endcan

                    @can('student_attendance_access')
                        <a href="{{ route('admin.student-attendances.index') }}"
                           class="sub-link {{ request()->is('admin/student-attendances*') ? 'active' : '' }}">
                            <i class="fas fa-user-check"></i>
                            Student Attendance
                        </a>
                    @endcan

                    @can('teacher_attendance_access')
                        <a href="{{ route('admin.teacher-attendances.index') }}"
                           class="sub-link {{ request()->is('admin/teacher-attendances*') ? 'active' : '' }}">
                            <i class="fas fa-chalkboard-teacher"></i>
                            Teacher Attendance
                        </a>
                    @endcan

                    @can('staff_attendance_access')
                        <a href="{{ route('admin.staff-attendances.index') }}"
                           class="sub-link {{ request()->is('admin/staff-attendances*') ? 'active' : '' }}">
                            <i class="fas fa-user-clock"></i>
                            Staff Attendance
                        </a>
                    @endcan

                    @can('staff_timetable_access')
                        <a href="{{ route('admin.staff-timetables.index') }}"
                           class="sub-link {{ request()->is('admin/staff-timetables*') ? 'active' : '' }}">
                            <i class="fas fa-calendar-day"></i>
                            Staff Timetable
                        </a>
                    @endcan

                    @can('holiday_access')
                        <a href="{{ route('admin.holidays.index') }}"
                           class="sub-link {{ request()->is('admin/holidays*') ? 'active' : '' }}">
                            <i class="fas fa-umbrella-beach"></i>
                            Holidays
                        </a>
                    @endcan

                    @can('faculty_log_access')
                        <a href="{{ route('admin.faculty-log-books.index') }}"
                           class="sub-link {{ request()->is('admin/faculty-log-books*') ? 'active' : '' }}">
                            <i class="fas fa-clipboard-check"></i>
                            Faculty Log Book
                        </a>
                    @endcan

                    @can('extra_class_access')
                        <a href="{{ route('admin.extra-classes.index') }}"
                           class="sub-link {{ request()->is('admin/extra-classes*') ? 'active' : '' }}">
                            <i class="fas fa-clock"></i>
                            Extra Classes
                        </a>
                    @endcan

                </div>
            </div>
        @endcanany

        {{-- COMMUNICATION GROUP --}}
        @canany(['notice_access'])
            @php
                $communicationActive = request()->is('admin/notices*');
            @endphp

            <div x-data="{ open: {{ $communicationActive ? 'true' : 'false' }} }">
                <button type="button"
                        @click="open = !open"
                        data-tooltip="Communication"
                        class="nav-link nav-group-btn {{ $communicationActive ? 'active' : '' }}">

                    <div class="nav-group-left">
                        <i class="fas fa-bullhorn nav-icon"></i>
                        <span class="nav-label">Communication</span>
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

                    @can('notice_access')
                        <a href="{{ route('admin.notices.index') }}"
                           class="sub-link {{ request()->is('admin/notices*') ? 'active' : '' }}">
                            <i class="fas fa-bullhorn"></i>
                            Notices
                        </a>
                    @endcan

                </div>
            </div>
        @endcanany

        {{-- OPERATIONS GROUP --}}
        @canany(['maintenance_access', 'inventory_access'])
            @php
                $operationsActive = request()->is('admin/maintenance-requests*')
                    || request()->is('admin/inventory-items*');
            @endphp

            <div x-data="{ open: {{ $operationsActive ? 'true' : 'false' }} }">
                <button type="button"
                        @click="open = !open"
                        data-tooltip="Operations"
                        class="nav-link nav-group-btn {{ $operationsActive ? 'active' : '' }}">
                    <div class="nav-group-left">
                        <i class="fas fa-tools nav-icon"></i>
                        <span class="nav-label">Operations</span>
                    </div>
                    <i class="fas fa-chevron-right chevron" :style="open ? 'transform:rotate(90deg)' : ''"></i>
                </button>

                <div class="submenu" x-show="open">
                    @can('maintenance_access')
                        <a href="{{ route('admin.maintenance-requests.index') }}"
                           class="sub-link {{ request()->is('admin/maintenance-requests*') ? 'active' : '' }}">
                            <i class="fas fa-wrench"></i>
                            Maintenance
                        </a>
                    @endcan

                    @can('inventory_access')
                        <a href="{{ route('admin.inventory-items.index') }}"
                           class="sub-link {{ request()->is('admin/inventory-items*') ? 'active' : '' }}">
                            <i class="fas fa-boxes"></i>
                            Inventory
                        </a>
                    @endcan
                </div>
            </div>
        @endcanany

        {{-- INTEGRATIONS GROUP --}}
        @canany(['whatsapp_settings_access', 'whatsapp_logs_access', 'biometric_logs_access'])
            @php
                $integrationActive = request()->is('admin/whatsapp-settings*')
                    || request()->is('admin/whatsapp-logs*')
                    || request()->is('admin/biometric-logs*');
            @endphp

            <div x-data="{ open: {{ $integrationActive ? 'true' : 'false' }} }">
                <button type="button"
                        @click="open = !open"
                        data-tooltip="Integrations"
                        class="nav-link nav-group-btn {{ $integrationActive ? 'active' : '' }}">
                    <div class="nav-group-left">
                        <i class="fas fa-plug nav-icon"></i>
                        <span class="nav-label">Integrations</span>
                    </div>
                    <i class="fas fa-chevron-right chevron" :style="open ? 'transform:rotate(90deg)' : ''"></i>
                </button>

                <div class="submenu" x-show="open">
                    @can('whatsapp_settings_access')
                        <a href="{{ route('admin.whatsapp-settings.index') }}"
                           class="sub-link {{ request()->is('admin/whatsapp-settings*') ? 'active' : '' }}">
                            <i class="fab fa-whatsapp"></i>
                            WhatsApp Settings
                        </a>
                    @endcan

                    @can('whatsapp_logs_access')
                        <a href="{{ route('admin.whatsapp-logs.index') }}"
                           class="sub-link {{ request()->is('admin/whatsapp-logs*') ? 'active' : '' }}">
                            <i class="fas fa-comment-dots"></i>
                            WhatsApp Logs
                        </a>
                    @endcan

                    @can('biometric_logs_access')
                        <a href="{{ route('admin.biometric-logs.index') }}"
                           class="sub-link {{ request()->is('admin/biometric-logs*') ? 'active' : '' }}">
                            <i class="fas fa-fingerprint"></i>
                            Biometric Logs
                        </a>
                    @endcan
                </div>
            </div>
        @endcanany

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

<style>
.submenu .sub-link {
    margin-bottom: 4px;
}

.submenu .sub-link i {
    width: 18px;
    text-align: center;
}

.nav-group-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.nav-group-btn {
    width: 100%;
    border: 0;
    background: transparent;
    cursor: pointer;
}

.chevron {
    transition: .2s ease;
}
</style>
