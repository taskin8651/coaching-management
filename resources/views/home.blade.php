@extends('layouts.admin')

@section('page-title', 'Dashboard')

@section('content')
<style>
    .dashboard-hero {
    display: grid;
    grid-template-columns: 1.1fr 1fr;
    gap: 24px;
    padding: 28px;
    margin-bottom: 24px;
    border-radius: 24px;
    color: #fff;
    background:
        radial-gradient(circle at top left, rgba(255,255,255,.25), transparent 34%),
        linear-gradient(135deg, #0F172A, #1E293B 45%, #4F46E5);
    box-shadow: 0 24px 60px rgba(15, 23, 42, .22);
    overflow: hidden;
    position: relative;
}

.dashboard-hero::after {
    content: "";
    position: absolute;
    width: 260px;
    height: 260px;
    right: -90px;
    top: -90px;
    border-radius: 50%;
    background: rgba(255,255,255,.12);
}

.dashboard-hero-label {
    margin: 0 0 8px;
    font-size: 13px;
    font-weight: 700;
    color: #CBD5E1;
    text-transform: uppercase;
    letter-spacing: .08em;
}

.dashboard-hero-title {
    margin: 0;
    font-size: 42px;
    font-weight: 900;
    letter-spacing: -1px;
}

.dashboard-hero-text {
    margin: 8px 0 0;
    color: #CBD5E1;
    font-size: 14px;
}

.dashboard-hero-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    position: relative;
    z-index: 1;
}

.dashboard-hero-grid div {
    padding: 16px;
    border-radius: 18px;
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.18);
    backdrop-filter: blur(10px);
}

.dashboard-hero-grid span {
    display: block;
    font-size: 12px;
    color: #CBD5E1;
    margin-bottom: 6px;
}

.dashboard-hero-grid strong {
    display: block;
    font-size: 18px;
    font-weight: 900;
    color: #fff;
}

.dashboard-stats .stat-card {
    position: relative;
    overflow: hidden;
}

.stat-icon {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    margin-bottom: 12px;
    font-size: 17px;
}

.bg-indigo { background: #4F46E5; }
.bg-blue { background: #0EA5E9; }
.bg-green { background: #10B981; }
.bg-orange { background: #F59E0B; }
.bg-red { background: #EF4444; }
.bg-purple { background: #8B5CF6; }
.bg-pink { background: #EC4899; }
.bg-slate { background: #475569; }

.report-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 22px;
    margin-bottom: 22px;
}

.finance-list {
    padding: 8px 18px 18px;
}

.finance-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 14px 0;
    border-bottom: 1px solid #E2E8F0;
    font-size: 14px;
    color: #475569;
}

.finance-row:last-child {
    border-bottom: none;
}

.finance-row span {
    display: flex;
    align-items: center;
    gap: 8px;
}

.finance-row strong {
    color: #0F172A;
    font-weight: 900;
}

.finance-row.total {
    margin-top: 8px;
    padding: 16px;
    border-radius: 16px;
    background: #0F172A;
    color: #fff;
    border-bottom: none;
}

.finance-row.total strong {
    color: #fff;
    font-size: 18px;
}

.text-success { color: #166534 !important; }
.text-warning { color: #92400E !important; }
.text-danger { color: #991B1B !important; }

.mini-metric-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
    padding: 18px;
}

.mini-metric {
    padding: 18px;
    border-radius: 18px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
}

.mini-metric span {
    display: block;
    font-size: 12px;
    color: #64748B;
    margin-bottom: 6px;
}

.mini-metric strong {
    display: block;
    font-size: 26px;
    font-weight: 900;
    color: #0F172A;
}

.simple-progress-wrap {
    padding: 0 18px 18px;
}

.simple-progress-head {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    color: #475569;
    margin-bottom: 8px;
}

.simple-progress {
    height: 10px;
    border-radius: 999px;
    background: #E2E8F0;
    overflow: hidden;
}

.simple-progress span {
    display: block;
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(135deg, #10B981, #22C55E);
}

.ranking-list,
.activity-list {
    padding: 8px 18px 18px;
}

.ranking-row,
.activity-row {
    display: flex;
    align-items: center;
    gap: 14px;
    justify-content: space-between;
    padding: 14px 0;
    border-bottom: 1px solid #E2E8F0;
}

.ranking-row:last-child,
.activity-row:last-child {
    border-bottom: none;
}

.ranking-row p,
.activity-row p {
    margin: 0 0 4px;
    font-size: 14px;
    font-weight: 800;
    color: #0F172A;
}

.ranking-row span,
.activity-row span {
    font-size: 12px;
    color: #64748B;
}

.ranking-row strong {
    min-width: 38px;
    height: 38px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #F1F5F9;
    color: #0F172A;
    font-weight: 900;
}

.activity-row {
    justify-content: flex-start;
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

.empty-box {
    padding: 18px;
    border-radius: 16px;
    background: #F8FAFC;
    color: #64748B;
    font-size: 13px;
    text-align: center;
}

.monthly-chart {
    padding: 24px 18px 12px;
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
    height: 170px;
    display: flex;
    align-items: end;
    justify-content: center;
    gap: 4px;
    padding: 10px 4px;
    border-radius: 14px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
}

.bar {
    width: 8px;
    min-height: 4px;
    display: block;
    border-radius: 999px 999px 0 0;
}

.bar.fee { background: #10B981; }
.bar.expense { background: #EF4444; }
.bar.salary { background: #F59E0B; }

.month-bar p {
    margin: 8px 0 0;
    font-size: 11px;
    color: #64748B;
    font-weight: 700;
}

.chart-legend {
    display: flex;
    gap: 18px;
    padding: 0 18px 18px;
    font-size: 13px;
    color: #475569;
}

.chart-legend span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.legend-dot.fee { background: #10B981; }
.legend-dot.expense { background: #EF4444; }
.legend-dot.salary { background: #F59E0B; }

@media (max-width: 991px) {
    .dashboard-hero,
    .report-grid {
        grid-template-columns: 1fr;
    }

    .monthly-chart {
        overflow-x: auto;
        grid-template-columns: repeat(12, 56px);
    }
}

@media (max-width: 575px) {
    .dashboard-hero-grid,
    .mini-metric-grid {
        grid-template-columns: 1fr;
    }

    .dashboard-hero-title {
        font-size: 32px;
    }
}
</style>

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Dashboard</h2>
        <p class="admin-page-subtitle">
            Complete coaching management overview, finance summary and recent activities
        </p>
    </div>

    <div class="identity-card">
        <div class="identity-avatar" style="background:#4F46E5;">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
        </div>

        <div>
            <p class="identity-title">{{ auth()->user()->name }}</p>
            <p class="identity-subtitle">Welcome back</p>
        </div>
    </div>
</div>

<div class="dashboard-hero">
    <div>
        <p class="dashboard-hero-label">Net Balance</p>
        <h1 class="dashboard-hero-title">
            ₹{{ number_format($netBalance, 2) }}
        </h1>
        <p class="dashboard-hero-text">
            Fee Collection - Expenses - Salary Paid
        </p>
    </div>

    <div class="dashboard-hero-grid">
        <div>
            <span>Total Income</span>
            <strong>₹{{ number_format($totalFeeCollection, 0) }}</strong>
        </div>

        <div>
            <span>Expenses</span>
            <strong>₹{{ number_format($totalExpenses, 0) }}</strong>
        </div>

        <div>
            <span>Salary Paid</span>
            <strong>₹{{ number_format($totalSalaryPaid, 0) }}</strong>
        </div>

        <div>
            <span>Fee Due</span>
            <strong>₹{{ number_format($totalFeeDue, 0) }}</strong>
        </div>
    </div>
</div>

<div class="stats-grid dashboard-stats">
    <div class="stat-card">
        <div class="stat-icon bg-indigo">
            <i class="fas fa-school"></i>
        </div>
        <p class="stat-label">Branches</p>
        <p class="stat-value">{{ $totalBranches }}</p>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-blue">
            <i class="fas fa-book"></i>
        </div>
        <p class="stat-label">Courses</p>
        <p class="stat-value">{{ $totalCourses }}</p>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-purple">
            <i class="fas fa-layer-group"></i>
        </div>
        <p class="stat-label">Batches</p>
        <p class="stat-value">{{ $totalBatches }}</p>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-green">
            <i class="fas fa-user-graduate"></i>
        </div>
        <p class="stat-label">Students</p>
        <p class="stat-value">{{ $totalStudents }}</p>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-orange">
            <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <p class="stat-label">Teachers</p>
        <p class="stat-value">{{ $totalTeachers }}</p>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-slate">
            <i class="fas fa-user-tie"></i>
        </div>
        <p class="stat-label">Staff</p>
        <p class="stat-value">{{ $totalStaff }}</p>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-pink">
            <i class="fas fa-headset"></i>
        </div>
        <p class="stat-label">Enquiries</p>
        <p class="stat-value">{{ $totalEnquiries }}</p>
    </div>

    <div class="stat-card">
        <div class="stat-icon bg-red">
            <i class="fas fa-phone-volume"></i>
        </div>
        <p class="stat-label">Pending Follow-ups</p>
        <p class="stat-value">{{ $pendingFollowUps }}</p>
    </div>
</div>

<div class="report-grid">

    <div class="page-card">
        <div class="page-card-header">
            <p class="page-card-title">Financial Summary</p>
            <span class="page-card-note">
                <i class="fas fa-chart-line"></i>
                Current overall numbers
            </span>
        </div>

        <div class="finance-list">
            <div class="finance-row">
                <span>
                    <i class="fas fa-arrow-down text-success"></i>
                    Fee Collection
                </span>
                <strong class="text-success">₹{{ number_format($totalFeeCollection, 2) }}</strong>
            </div>

            <div class="finance-row">
                <span>
                    <i class="fas fa-clock text-warning"></i>
                    Fee Due
                </span>
                <strong class="text-warning">₹{{ number_format($totalFeeDue, 2) }}</strong>
            </div>

            <div class="finance-row">
                <span>
                    <i class="fas fa-file-invoice-dollar text-danger"></i>
                    Expenses
                </span>
                <strong class="text-danger">₹{{ number_format($totalExpenses, 2) }}</strong>
            </div>

            <div class="finance-row">
                <span>
                    <i class="fas fa-money-check-alt text-danger"></i>
                    Salary Paid
                </span>
                <strong class="text-danger">₹{{ number_format($totalSalaryPaid, 2) }}</strong>
            </div>

            <div class="finance-row">
                <span>
                    <i class="fas fa-hourglass-half text-warning"></i>
                    Salary Due
                </span>
                <strong class="text-warning">₹{{ number_format($totalSalaryDue, 2) }}</strong>
            </div>

            <div class="finance-row total">
                <span>
                    <i class="fas fa-wallet"></i>
                    Net Balance
                </span>
                <strong>₹{{ number_format($netBalance, 2) }}</strong>
            </div>
        </div>
    </div>

    <div class="page-card">
        <div class="page-card-header">
            <p class="page-card-title">Enquiry Summary</p>
            <span class="page-card-note">
                <i class="fas fa-headset"></i>
                Lead conversion overview
            </span>
        </div>

        <div class="mini-metric-grid">
            <div class="mini-metric">
                <span>Total</span>
                <strong>{{ $totalEnquiries }}</strong>
            </div>

            <div class="mini-metric">
                <span>New</span>
                <strong>{{ $newEnquiries }}</strong>
            </div>

            <div class="mini-metric">
                <span>Converted</span>
                <strong>{{ $convertedEnquiries }}</strong>
            </div>

            <div class="mini-metric">
                <span>Follow-ups</span>
                <strong>{{ $pendingFollowUps }}</strong>
            </div>
        </div>

        <div class="simple-progress-wrap">
            @php
                $conversionPercent = $totalEnquiries > 0 ? round(($convertedEnquiries / $totalEnquiries) * 100) : 0;
            @endphp

            <div class="simple-progress-head">
                <span>Conversion Rate</span>
                <strong>{{ $conversionPercent }}%</strong>
            </div>

            <div class="simple-progress">
                <span style="width: {{ $conversionPercent }}%;"></span>
            </div>
        </div>
    </div>

</div>

<div class="report-grid">

    <div class="page-card">
        <div class="page-card-header">
            <p class="page-card-title">Branch-wise Students</p>
        </div>

        <div class="ranking-list">
            @forelse($branchWiseStudents as $branch)
                <div class="ranking-row">
                    <div>
                        <p>{{ $branch->name }}</p>
                        <span>{{ $branch->students_count }} students</span>
                    </div>

                    <strong>{{ $branch->students_count }}</strong>
                </div>
            @empty
                <div class="empty-box">No branch data found.</div>
            @endforelse
        </div>
    </div>

    <div class="page-card">
        <div class="page-card-header">
            <p class="page-card-title">Course-wise Students</p>
        </div>

        <div class="ranking-list">
            @forelse($courseWiseStudents as $course)
                <div class="ranking-row">
                    <div>
                        <p>{{ $course->name }}</p>
                        <span>{{ $course->students_count }} students</span>
                    </div>

                    <strong>{{ $course->students_count }}</strong>
                </div>
            @empty
                <div class="empty-box">No course data found.</div>
            @endforelse
        </div>
    </div>

</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">Monthly Finance Overview</p>

        <span class="page-card-note">
            <i class="fas fa-chart-bar"></i>
            Fee, Expense and Salary trend
        </span>
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

    <div class="chart-legend">
        <span><i class="legend-dot fee"></i> Fee</span>
        <span><i class="legend-dot expense"></i> Expense</span>
        <span><i class="legend-dot salary"></i> Salary</span>
    </div>
</div>

<div class="report-grid">

    <div class="page-card">
        <div class="page-card-header">
            <p class="page-card-title">Upcoming Exams</p>

            @can('exam_access')
                <a href="{{ route('admin.exams.index') }}" class="page-card-note">
                    View All
                </a>
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
                        </span>
                    </div>
                </div>
            @empty
                <div class="empty-box">No upcoming exams found.</div>
            @endforelse
        </div>
    </div>

    <div class="page-card">
        <div class="page-card-header">
            <p class="page-card-title">Recent Notices</p>

            @can('notice_access')
                <a href="{{ route('admin.notices.index') }}" class="page-card-note">
                    View All
                </a>
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
                <div class="empty-box">No recent notices found.</div>
            @endforelse
        </div>
    </div>

</div>

<div class="report-grid">

    <div class="page-card">
        <div class="page-card-header">
            <p class="page-card-title">Recent Fee Payments</p>

            @can('fee_payment_access')
                <a href="{{ route('admin.fee-payments.index') }}" class="page-card-note">
                    View All
                </a>
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
                            <strong>₹{{ number_format($payment->paid_amount, 0) }}</strong>
                        </p>
                        <span>
                            {{ $payment->receipt_no ?? '-' }}
                            • {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') : '-' }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="empty-box">No fee payments found.</div>
            @endforelse
        </div>
    </div>

    <div class="page-card">
        <div class="page-card-header">
            <p class="page-card-title">Recent Enquiries</p>

            @can('enquiry_access')
                <a href="{{ route('admin.enquiries.index') }}" class="page-card-note">
                    View All
                </a>
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
                        </span>
                    </div>
                </div>
            @empty
                <div class="empty-box">No enquiries found.</div>
            @endforelse
        </div>
    </div>

</div>

@endsection