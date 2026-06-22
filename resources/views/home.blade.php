@extends('layouts.admin')

@section('page-title', 'Dashboard')

@section('styles')
<style>
    .dash-wrap {
        font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
    }

    .dash-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 22px;
    }

    .dash-title {
        margin: 0;
        font-size: 24px;
        font-weight: 900;
        color: #0f172a;
        letter-spacing: -0.5px;
    }

    .dash-subtitle {
        margin: 6px 0 0;
        font-size: 13px;
        color: #64748b;
    }

    .role-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 999px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #334155;
        font-size: 13px;
        font-weight: 800;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
    }

    .dash-hero {
        display: grid;
        grid-template-columns: 1.1fr 1fr;
        gap: 20px;
        padding: 26px;
        border-radius: 26px;
        margin-bottom: 22px;
        color: #fff;
        background:
            radial-gradient(circle at 15% 10%, rgba(255,255,255,.26), transparent 28%),
            linear-gradient(135deg, #0f172a, #1e293b 48%, #4f46e5);
        box-shadow: 0 26px 70px rgba(15, 23, 42, 0.22);
        overflow: hidden;
        position: relative;
    }

    .dash-hero::after {
        content: "";
        position: absolute;
        right: -90px;
        top: -90px;
        width: 260px;
        height: 260px;
        border-radius: 50%;
        background: rgba(255,255,255,.12);
    }

    .hero-label {
        margin: 0 0 8px;
        font-size: 12px;
        font-weight: 900;
        color: #cbd5e1;
        text-transform: uppercase;
        letter-spacing: .09em;
    }

    .hero-title {
        margin: 0;
        font-size: 40px;
        font-weight: 900;
        letter-spacing: -1px;
    }

    .hero-text {
        margin: 10px 0 0;
        color: #cbd5e1;
        font-size: 14px;
    }

    .hero-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        position: relative;
        z-index: 2;
    }

    .hero-mini {
        padding: 16px;
        border-radius: 18px;
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.18);
        backdrop-filter: blur(10px);
    }

    .hero-mini span {
        display: block;
        font-size: 12px;
        color: #cbd5e1;
        margin-bottom: 6px;
    }

    .hero-mini strong {
        display: block;
        color: #fff;
        font-size: 18px;
        font-weight: 900;
    }

    .stat-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 22px;
    }

    .dash-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 18px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
        transition: .22s ease;
    }

    .dash-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
    }

    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        margin-bottom: 14px;
        font-size: 18px;
    }

    .stat-label {
        margin: 0;
        color: #64748b;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .stat-value {
        margin: 7px 0 0;
        color: #0f172a;
        font-size: 28px;
        font-weight: 900;
    }

    .bg-indigo { background: #4f46e5; }
    .bg-blue { background: #0ea5e9; }
    .bg-green { background: #10b981; }
    .bg-orange { background: #f59e0b; }
    .bg-red { background: #ef4444; }
    .bg-purple { background: #8b5cf6; }
    .bg-pink { background: #ec4899; }
    .bg-slate { background: #475569; }

    .dash-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 22px;
    }

    .panel-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 22px;
        overflow: hidden;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
    }

    .panel-head {
        padding: 18px 20px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .panel-title {
        margin: 0;
        color: #0f172a;
        font-size: 15px;
        font-weight: 900;
    }

    .panel-note {
        font-size: 12px;
        color: #64748b;
        text-decoration: none;
        font-weight: 700;
    }

    .finance-list {
        padding: 8px 20px 20px;
    }

    .finance-row {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        padding: 14px 0;
        border-bottom: 1px solid #e2e8f0;
        color: #475569;
        font-size: 14px;
    }

    .finance-row:last-child {
        border-bottom: none;
    }

    .finance-row strong {
        color: #0f172a;
        font-weight: 900;
    }

    .finance-row.total {
        margin-top: 8px;
        padding: 16px;
        border-radius: 16px;
        background: #0f172a;
        color: #fff;
        border: none;
    }

    .finance-row.total strong {
        color: #fff;
    }

    .activity-list {
        padding: 8px 20px 20px;
    }

    .activity-row {
        display: flex;
        gap: 13px;
        align-items: center;
        padding: 13px 0;
        border-bottom: 1px solid #e2e8f0;
    }

    .activity-row:last-child {
        border-bottom: none;
    }

    .activity-icon {
        width: 42px;
        height: 42px;
        min-width: 42px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
    }

    .activity-row p {
        margin: 0 0 4px;
        font-size: 14px;
        color: #0f172a;
        font-weight: 900;
    }

    .activity-row span {
        font-size: 12px;
        color: #64748b;
    }

    .empty-box {
        margin: 18px;
        padding: 18px;
        border-radius: 16px;
        background: #f8fafc;
        color: #64748b;
        text-align: center;
        font-size: 13px;
        font-weight: 700;
    }

    .monthly-chart {
        padding: 24px 20px 12px;
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 12px;
        min-height: 240px;
        align-items: end;
    }

    .month-bar {
        text-align: center;
    }

    .bar-box {
        height: 160px;
        display: flex;
        align-items: end;
        justify-content: center;
        gap: 4px;
        padding: 9px 4px;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }

    .bar {
        width: 8px;
        min-height: 4px;
        display: block;
        border-radius: 999px 999px 0 0;
    }

    .bar.fee { background: #10b981; }
    .bar.expense { background: #ef4444; }
    .bar.salary { background: #f59e0b; }

    .month-bar p {
        margin: 8px 0 0;
        font-size: 11px;
        color: #64748b;
        font-weight: 800;
    }

    .legend {
        display: flex;
        gap: 18px;
        padding: 0 20px 20px;
        font-size: 13px;
        color: #475569;
    }

    .legend span {
        display: flex;
        gap: 7px;
        align-items: center;
    }

    .legend i {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }

    .dot-fee { background: #10b981; }
    .dot-expense { background: #ef4444; }
    .dot-salary { background: #f59e0b; }

    @media(max-width: 1100px) {
        .stat-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .dash-hero,
        .dash-grid-2 {
            grid-template-columns: 1fr;
        }
    }

    @media(max-width: 600px) {
        .stat-grid,
        .hero-grid {
            grid-template-columns: 1fr;
        }

        .dash-head {
            flex-direction: column;
            align-items: flex-start;
        }

        .hero-title {
            font-size: 30px;
        }

        .monthly-chart {
            overflow-x: auto;
            grid-template-columns: repeat(12, 54px);
        }
    }
</style>
@endsection

@section('content')

<div class="dash-wrap">

    <div class="dash-head">
        <div>
            <h2 class="dash-title">Dashboard</h2>
            <p class="dash-subtitle">
                Welcome back, <strong>{{ auth()->user()->name }}</strong>. Role-wise smart dashboard overview.
            </p>
        </div>

        <div class="role-chip">
            <i class="fas fa-user-shield"></i>
            {{ $scope['role_label'] }}
        </div>
    </div>

    <div class="dash-hero">
        <div>
            @if($scope['is_student'])
                <p class="hero-label">My Fee Summary</p>
                <h1 class="hero-title">₹{{ number_format($totalFeeDue, 2) }}</h1>
                <p class="hero-text">Your pending fee due amount.</p>
            @elseif($scope['is_teacher'])
                <p class="hero-label">My Students</p>
                <h1 class="hero-title">{{ $myStudents->count() }}</h1>
                <p class="hero-text">Students visible under your assigned branch/batches.</p>
            @else
                <p class="hero-label">Net Balance</p>
                <h1 class="hero-title">₹{{ number_format($netBalance, 2) }}</h1>
                <p class="hero-text">Fee Collection - Expenses - Salary Paid</p>
            @endif
        </div>

        <div class="hero-grid">
            <div class="hero-mini">
                <span>Fee Collection</span>
                <strong>₹{{ number_format($totalFeeCollection, 0) }}</strong>
            </div>

            <div class="hero-mini">
                <span>Fee Due</span>
                <strong>₹{{ number_format($totalFeeDue, 0) }}</strong>
            </div>

            <div class="hero-mini">
                <span>Exams</span>
                <strong>{{ $completedExams }} Completed</strong>
            </div>

            <div class="hero-mini">
                <span>Upcoming</span>
                <strong>{{ $upcomingExamsCount }} Exams</strong>
            </div>
        </div>
    </div>

    <div class="stat-grid">
        @foreach($cards as $card)
            @if($card['show'])
                <div class="dash-card">
                    <div class="stat-icon bg-{{ $card['color'] }}">
                        <i class="{{ $card['icon'] }}"></i>
                    </div>

                    <p class="stat-label">{{ $card['title'] }}</p>
                    <p class="stat-value">{{ $card['value'] }}</p>
                </div>
            @endif
        @endforeach
    </div>

    @if(!$scope['is_student'] && !$scope['is_teacher'])
        <div class="dash-grid-2">
            <div class="panel-card">
                <div class="panel-head">
                    <p class="panel-title">Financial Summary</p>
                    <span class="panel-note">Live Overview</span>
                </div>

                <div class="finance-list">
                    <div class="finance-row">
                        <span>Fee Collection</span>
                        <strong>₹{{ number_format($totalFeeCollection, 2) }}</strong>
                    </div>

                    <div class="finance-row">
                        <span>Fee Due</span>
                        <strong>₹{{ number_format($totalFeeDue, 2) }}</strong>
                    </div>

                    <div class="finance-row">
                        <span>Expenses</span>
                        <strong>₹{{ number_format($totalExpenses, 2) }}</strong>
                    </div>

                    <div class="finance-row">
                        <span>Salary Paid</span>
                        <strong>₹{{ number_format($totalSalaryPaid, 2) }}</strong>
                    </div>

                    <div class="finance-row">
                        <span>Salary Due</span>
                        <strong>₹{{ number_format($totalSalaryDue, 2) }}</strong>
                    </div>

                    <div class="finance-row total">
                        <span>Net Balance</span>
                        <strong>₹{{ number_format($netBalance, 2) }}</strong>
                    </div>
                </div>
            </div>

            <div class="panel-card">
                <div class="panel-head">
                    <p class="panel-title">Enquiry Summary</p>

                    @can('enquiry_access')
                        <a href="{{ route('admin.enquiries.index') }}" class="panel-note">View All</a>
                    @endcan
                </div>

                <div class="finance-list">
                    <div class="finance-row">
                        <span>Total Enquiries</span>
                        <strong>{{ $totalEnquiries }}</strong>
                    </div>

                    <div class="finance-row">
                        <span>New Enquiries</span>
                        <strong>{{ $newEnquiries }}</strong>
                    </div>

                    <div class="finance-row">
                        <span>Converted</span>
                        <strong>{{ $convertedEnquiries }}</strong>
                    </div>

                    <div class="finance-row total">
                        <span>Pending Follow-ups</span>
                        <strong>{{ $pendingFollowUps }}</strong>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="panel-card" style="margin-bottom:22px;">
        <div class="panel-head">
            <p class="panel-title">Monthly Finance Overview</p>
            <span class="panel-note">Fee / Expense / Salary</span>
        </div>

        <div class="monthly-chart">
            @foreach($months as $item)
                @php
                    $maxValue = max($item['fee'], $item['expense'], $item['salary'], 1);
                    $feeHeight = ($item['fee'] / $maxValue) * 100;
                    $expenseHeight = ($item['expense'] / $maxValue) * 100;
                    $salaryHeight = ($item['salary'] / $maxValue) * 100;
                @endphp

                <div class="month-bar">
                    <div class="bar-box">
                        <span class="bar fee" style="height: {{ $feeHeight }}%;"></span>
                        <span class="bar expense" style="height: {{ $expenseHeight }}%;"></span>
                        <span class="bar salary" style="height: {{ $salaryHeight }}%;"></span>
                    </div>

                    <p>{{ $item['month'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="legend">
            <span><i class="dot-fee"></i> Fee</span>
            <span><i class="dot-expense"></i> Expense</span>
            <span><i class="dot-salary"></i> Salary</span>
        </div>
    </div>

    <div class="dash-grid-2">

        @if(!$scope['is_student'])
            <div class="panel-card">
                <div class="panel-head">
                    <p class="panel-title">
                        {{ $scope['is_teacher'] ? 'My Students' : 'Recent Students' }}
                    </p>

                    @can('student_access')
                        <a href="{{ route('admin.students.index') }}" class="panel-note">View All</a>
                    @endcan
                </div>

                <div class="activity-list">
                    @forelse($myStudents as $student)
                        <div class="activity-row">
                            <div class="activity-icon bg-green">
                                <i class="fas fa-user-graduate"></i>
                            </div>

                            <div>
                                <p>{{ $student->user->name ?? 'Student' }}</p>
                                <span>
                                    {{ $student->student_code ?? '-' }}
                                    • {{ $student->course->name ?? '-' }}
                                    • {{ $student->batch->name ?? '-' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="empty-box">No students found.</div>
                    @endforelse
                </div>
            </div>
        @endif

        <div class="panel-card">
            <div class="panel-head">
                <p class="panel-title">Upcoming Exams</p>

                @can('exam_access')
                    <a href="{{ route('admin.exams.index') }}" class="panel-note">View All</a>
                @endcan
            </div>

            <div class="activity-list">
                @forelse($upcomingExams as $exam)
                    <div class="activity-row">
                        <div class="activity-icon bg-purple">
                            <i class="fas fa-clipboard-list"></i>
                        </div>

                        <div>
                            <p>{{ $exam->title }}</p>
                            <span>
                                {{ $exam->exam_date ? \Carbon\Carbon::parse($exam->exam_date)->format('d M Y') : '-' }}
                                • {{ $exam->batch->name ?? '-' }}
                                • {{ $exam->subject->name ?? '-' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="empty-box">No upcoming exams found.</div>
                @endforelse
            </div>
        </div>

    </div>

    <div class="dash-grid-2">

        <div class="panel-card">
            <div class="panel-head">
                <p class="panel-title">Study Materials</p>

                @can('study_material_access')
                    <a href="{{ route('admin.study-materials.index') }}" class="panel-note">View All</a>
                @endcan
            </div>

            <div class="activity-list">
                @forelse($recentStudyMaterials as $material)
                    <div class="activity-row">
                        <div class="activity-icon bg-blue">
                            <i class="fas fa-book-reader"></i>
                        </div>

                        <div>
                            <p>{{ $material->title }}</p>
                            <span>
                                {{ $material->material_type ?? 'Material' }}
                                • {{ $material->course->name ?? '-' }}
                                • {{ $material->batch->name ?? '-' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="empty-box">No study materials found.</div>
                @endforelse
            </div>
        </div>

        <div class="panel-card">
            <div class="panel-head">
                <p class="panel-title">Recent Notices</p>

                @can('notice_access')
                    <a href="{{ route('admin.notices.index') }}" class="panel-note">View All</a>
                @endcan
            </div>

            <div class="activity-list">
                @forelse($recentNotices as $notice)
                    <div class="activity-row">
                        <div class="activity-icon bg-orange">
                            <i class="fas fa-bullhorn"></i>
                        </div>

                        <div>
                            <p>{{ $notice->title }}</p>
                            <span>
                                {{ $notice->notice_type ?? 'Notice' }}
                                • {{ $notice->publish_date ? \Carbon\Carbon::parse($notice->publish_date)->format('d M Y') : '-' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="empty-box">No notices found.</div>
                @endforelse
            </div>
        </div>

    </div>

    @if(!$scope['is_teacher'])
        <div class="dash-grid-2">

            <div class="panel-card">
                <div class="panel-head">
                    <p class="panel-title">
                        {{ $scope['is_student'] ? 'My Fee Payments' : 'Recent Fee Payments' }}
                    </p>

                    @can('fee_payment_access')
                        <a href="{{ route('admin.fee-payments.index') }}" class="panel-note">View All</a>
                    @endcan
                </div>

                <div class="activity-list">
                    @forelse($recentFeePayments as $payment)
                        <div class="activity-row">
                            <div class="activity-icon bg-green">
                                <i class="fas fa-rupee-sign"></i>
                            </div>

                            <div>
                                <p>
                                    {{ $payment->student->user->name ?? 'Student' }}
                                    — ₹{{ number_format($payment->paid_amount, 0) }}
                                </p>
                                <span>
                                    {{ $payment->receipt_no ?? '-' }}
                                    • Due ₹{{ number_format($payment->due_amount, 0) }}
                                    • {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') : '-' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="empty-box">No fee payments found.</div>
                    @endforelse
                </div>
            </div>

            @if(!$scope['is_student'])
                <div class="panel-card">
                    <div class="panel-head">
                        <p class="panel-title">Recent Enquiries</p>

                        @can('enquiry_access')
                            <a href="{{ route('admin.enquiries.index') }}" class="panel-note">View All</a>
                        @endcan
                    </div>

                    <div class="activity-list">
                        @forelse($recentEnquiries as $enquiry)
                            <div class="activity-row">
                                <div class="activity-icon bg-pink">
                                    <i class="fas fa-headset"></i>
                                </div>

                                <div>
                                    <p>{{ $enquiry->student_name }}</p>
                                    <span>
                                        {{ $enquiry->phone }}
                                        • {{ ucwords(str_replace('_', ' ', $enquiry->status)) }}
                                        • {{ $enquiry->branch->name ?? '-' }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="empty-box">No enquiries found.</div>
                        @endforelse
                    </div>
                </div>
            @endif

        </div>
    @endif

</div>


@endsection