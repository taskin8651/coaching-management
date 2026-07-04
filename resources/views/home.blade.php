@extends('layouts.admin')

@section('page-title', 'Dashboard')

@section('styles')
<style>
    .ed-wrap{font-family:'Plus Jakarta Sans',system-ui,sans-serif;color:#111827}
    .ed-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;gap:16px}
    .ed-title{font-size:25px;font-weight:900;letter-spacing:-.04em;margin:0;color:#0f172a}
    .ed-crumb{font-size:12px;color:#64748b;margin-top:5px}
    .date-pill{display:flex;align-items:center;gap:10px;background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:12px 16px;font-weight:800;color:#334155;box-shadow:0 8px 24px rgba(15,23,42,.05)}
    .metric-grid{display:grid;grid-template-columns:repeat(8,minmax(0,1fr));gap:14px;margin-bottom:18px}
    .metric-card{background:#fff;border:1px solid #e5e7eb;border-radius:18px;padding:16px;box-shadow:0 10px 28px rgba(15,23,42,.045)}
    .metric-top{display:flex;gap:13px;align-items:center}
    .metric-icon{width:50px;height:50px;border-radius:999px;display:grid;place-items:center;font-size:20px}
    .tone-blue{background:#dbeafe;color:#2563eb}.tone-green{background:#dcfce7;color:#16a34a}.tone-purple{background:#f3e8ff;color:#9333ea}.tone-amber{background:#fef3c7;color:#d97706}.tone-cyan{background:#cffafe;color:#0891b2}.tone-emerald{background:#dcfce7;color:#15803d}.tone-orange{background:#ffedd5;color:#ea580c}.tone-pink{background:#fce7f3;color:#db2777}
    .metric-value{font-size:24px;font-weight:900;margin:0;color:#111827;line-height:1}
    .metric-label{font-size:12px;color:#64748b;font-weight:700;margin:5px 0 0}.metric-trend{font-size:11px;color:#16a34a;margin:12px 0 0;font-weight:800}
    .ed-grid{display:grid;grid-template-columns:1.2fr 1.1fr 1.5fr 1.25fr 1fr;gap:14px;margin-bottom:14px}
    .ed-card{background:#fff;border:1px solid #e5e7eb;border-radius:18px;box-shadow:0 10px 28px rgba(15,23,42,.045);overflow:hidden}
    .card-head{display:flex;justify-content:space-between;align-items:center;padding:16px 18px;border-bottom:1px solid #eef2f7}
    .card-title{font-size:14px;font-weight:900;margin:0;color:#0f172a}.view-link{font-size:12px;color:#2563eb;font-weight:800;text-decoration:none}
    .donut-row{align-items:center;gap:22px;padding:22px 20px 20px;min-height:206px}
    .donut-stage{position:relative;display:grid;place-items:center;min-width:168px}
    .donut-stage:before{content:"";position:absolute;width:166px;height:166px;border-radius:42px;background:linear-gradient(145deg,#f8fafc,#eef2ff);filter:blur(.1px);box-shadow:inset 0 1px 0 rgba(255,255,255,.9),0 18px 45px rgba(15,23,42,.08)}
    .donut{--chart:#e5e7eb;width:148px;height:148px;border-radius:50%;display:grid;place-items:center;background:var(--chart);position:relative;box-shadow:0 16px 38px rgba(15,23,42,.14),inset 0 1px 0 rgba(255,255,255,.85)}
    .donut:before{content:"";position:absolute;width:96px;height:96px;border-radius:50%;background:linear-gradient(180deg,#fff,#f8fafc);box-shadow:inset 0 0 0 1px #e5e7eb,0 8px 18px rgba(15,23,42,.08)}
    .donut span{position:relative;text-align:center;font-size:11px;color:#64748b;font-weight:900;text-transform:uppercase;letter-spacing:.04em}.donut strong{display:block;color:#0f172a;font-size:18px;margin-top:4px;letter-spacing:-.04em;text-transform:none}.donut small{display:block;color:#16a34a;font-size:11px;margin-top:2px;text-transform:none;letter-spacing:0}
    .legend-list{flex:1;display:grid;gap:12px}.legend-item{display:block;padding:12px 13px;border:1px solid #eef2f7;border-radius:16px;background:linear-gradient(180deg,#fff,#f8fafc);font-size:13px;color:#334155;box-shadow:0 8px 18px rgba(15,23,42,.035)}.legend-item-top{display:flex;justify-content:space-between;gap:12px;align-items:center}.legend-item strong{color:#0f172a;font-weight:900}.legend-dot{width:10px;height:10px;border-radius:50%;display:inline-block;margin-right:8px;box-shadow:0 0 0 4px rgba(148,163,184,.13)}.legend-progress{height:7px;border-radius:999px;background:#e5e7eb;overflow:hidden;margin-top:10px}.legend-progress span{display:block;height:100%;border-radius:999px}.green{background:#22c55e}.yellow{background:#eab308}.red{background:#ef4444}.legend-progress .green{background:linear-gradient(90deg,#86efac,#16a34a)}.legend-progress .yellow{background:linear-gradient(90deg,#fde68a,#ca8a04)}.legend-progress .red{background:linear-gradient(90deg,#fca5a5,#dc2626)}
    .bar-chart{height:185px;padding:18px;display:grid;grid-template-columns:repeat(12,1fr);gap:8px;align-items:end}.bar-col{text-align:center}.bar-line{height:135px;background:#f8fafc;border-radius:10px;display:flex;align-items:end;justify-content:center}.bar-fill{width:18px;border-radius:8px 8px 2px 2px;background:linear-gradient(180deg,#60a5fa,#2563eb);min-height:4px}.bar-label{font-size:10px;color:#64748b;margin-top:7px}
    .line-chart{height:185px;padding:16px 18px;display:flex;align-items:end;gap:8px}.line-point{flex:1;text-align:center}.point-dot{height:8px;width:8px;border-radius:50%;background:#7c3aed;margin:0 auto 7px}.line-stick{width:3px;background:#ddd6fe;margin:0 auto;border-radius:999px}.line-label{font-size:10px;color:#64748b}
    .notice-list,.portal-body,.activity-list{padding:14px 16px}.notice-item,.activity-item{display:flex;gap:12px;padding:12px 0;border-bottom:1px solid #eef2f7}.notice-item:last-child,.activity-item:last-child{border-bottom:0}.mini-icon{width:38px;height:38px;border-radius:12px;display:grid;place-items:center;flex:0 0 auto}.item-title{font-size:13px;font-weight:900;margin:0;color:#0f172a}.item-meta{font-size:11px;color:#64748b;margin-top:4px}
    .wide-grid{display:grid;grid-template-columns:1.1fr 1.45fr 1.45fr;gap:14px;margin-bottom:14px}.bottom-grid{display:grid;grid-template-columns:2fr .95fr 1fr;gap:14px}
    .mini-table{width:100%;font-size:12px}.mini-table th{background:#f8fafc;color:#475569;text-align:left;padding:10px;font-weight:900}.mini-table td{padding:10px;border-top:1px solid #eef2f7;color:#334155}.status-pill-sm{font-size:10px;border-radius:999px;padding:5px 9px;font-weight:900}.live{background:#dcfce7;color:#15803d}.upcoming{background:#dbeafe;color:#2563eb}
    .exam-score{font-size:34px;font-weight:900;color:#0f172a}.score-box{padding:20px}.score-line{height:110px;display:flex;align-items:end;gap:16px;margin-top:12px}.score-month{flex:1;text-align:center}.score-bar{height:75px;border-left:2px solid #2563eb;border-top:2px solid #2563eb;transform:skewY(-12deg);border-radius:4px}
    .calendar{padding:16px}.cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:8px;text-align:center;font-size:12px}.cal-day{padding:7px;border-radius:10px;color:#334155}.cal-active{background:#2563eb;color:#fff;font-weight:900}.portal-card{background:linear-gradient(180deg,#eef4ff,#fff)}
    .portal-meter{width:96px;height:96px;border-radius:50%;background:conic-gradient(#22c55e {{ $attendancePercent }}%,#e5e7eb 0);display:grid;place-items:center;margin:4px auto 16px;position:relative}.portal-meter:before{content:"";position:absolute;width:70px;height:70px;background:#fff;border-radius:50%}.portal-meter strong{position:relative;font-size:18px}
    .portal-row{display:flex;justify-content:space-between;background:#fff;border-radius:14px;padding:12px;margin-top:10px;font-size:12px;font-weight:800;color:#334155}.portal-btn{display:block;text-align:center;margin-top:14px;border:1px solid #2563eb;border-radius:12px;color:#2563eb;padding:11px;font-weight:900;text-decoration:none}
    @media(max-width:1500px){.metric-grid{grid-template-columns:repeat(4,1fr)}.ed-grid,.wide-grid,.bottom-grid{grid-template-columns:1fr 1fr}.right-only{grid-column:span 2}}
    @media(max-width:900px){.metric-grid,.ed-grid,.wide-grid,.bottom-grid{grid-template-columns:1fr}.right-only{grid-column:auto}.ed-head{align-items:flex-start;flex-direction:column}}
</style>
@endsection

@section('content')
@php
    $maxFee = max($monthlyFee->max('value') ?? 0, 1);
    $maxEnquiry = max($monthlyEnquiries->max('value') ?? 0, 1);
    $calendarStart = now()->copy()->startOfMonth();
    $daysInMonth = now()->daysInMonth;
@endphp

<div class="ed-wrap">
    <div class="ed-head">
        <div>
            <h1 class="ed-title">Dashboard</h1>
            <div class="ed-crumb">{{ $scope['role_label'] }} / {{ auth()->user()->name }}</div>
        </div>
        <div class="date-pill"><i class="fas fa-calendar-days"></i>{{ now()->format('d M Y, l') }}</div>
    </div>

    <div class="metric-grid">
        @foreach($topCards as $card)
            @if($card['show'])
                <div class="metric-card">
                    <div class="metric-top">
                        <div class="metric-icon tone-{{ $card['tone'] }}"><i class="{{ $card['icon'] }}"></i></div>
                        <div>
                            <p class="metric-value">{{ $card['value'] }}</p>
                            <p class="metric-label">{{ $card['title'] }}</p>
                        </div>
                    </div>
                    <p class="metric-trend"><i class="fas fa-arrow-up"></i> live scoped data</p>
                </div>
            @endif
        @endforeach
    </div>

    <div class="ed-grid">
        <div class="ed-card">
            <div class="card-head"><p class="card-title">Fee Collection Status</p></div>
            <div class="donut-row">
                <div class="donut-stage">
                    <div class="donut" style="--chart: conic-gradient(#22c55e 0 {{ $feePaidPercent }}%, #ef4444 {{ $feePaidPercent }}% 100%);">
                        <span>
                            Total
                            <strong>₹{{ number_format($feeCollected + $pendingFees,0) }}</strong>
                            <small>{{ $feePaidPercent }}% paid</small>
                        </span>
                    </div>
                </div>
                <div class="legend-list">
                    <div class="legend-item">
                        <div class="legend-item-top">
                            <span><i class="legend-dot green"></i>Paid</span>
                            <strong>₹{{ number_format($feeCollected,0) }} · {{ $feePaidPercent }}%</strong>
                        </div>
                        <div class="legend-progress"><span class="green" style="width:{{ $feePaidPercent }}%"></span></div>
                    </div>
                    <div class="legend-item">
                        <div class="legend-item-top">
                            <span><i class="legend-dot red"></i>Due</span>
                            <strong>₹{{ number_format($pendingFees,0) }} · {{ $feeDuePercent }}%</strong>
                        </div>
                        <div class="legend-progress"><span class="red" style="width:{{ $feeDuePercent }}%"></span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="ed-card">
            <div class="card-head"><p class="card-title">Student Attendance Today</p></div>
            <div class="donut-row">
                @php
                    $lateStart = $attendancePercent;
                    $lateEnd = $attendancePercent + $latePercent;
                    $absentStart = $lateEnd;
                @endphp
                <div class="donut-stage">
                    <div class="donut" style="--chart: conic-gradient(#22c55e 0 {{ $attendancePercent }}%, #eab308 {{ $lateStart }}% {{ $lateEnd }}%, #ef4444 {{ $absentStart }}% 100%);">
                        <span>
                            Total
                            <strong>{{ $attendanceTotalToday }}</strong>
                            <small>{{ $attendancePercent }}% present</small>
                        </span>
                    </div>
                </div>
                <div class="legend-list">
                    <div class="legend-item">
                        <div class="legend-item-top">
                            <span><i class="legend-dot green"></i>Present</span>
                            <strong>{{ $presentToday }} · {{ $attendancePercent }}%</strong>
                        </div>
                        <div class="legend-progress"><span class="green" style="width:{{ $attendancePercent }}%"></span></div>
                    </div>
                    <div class="legend-item">
                        <div class="legend-item-top">
                            <span><i class="legend-dot yellow"></i>Late</span>
                            <strong>{{ $lateToday }} · {{ $latePercent }}%</strong>
                        </div>
                        <div class="legend-progress"><span class="yellow" style="width:{{ $latePercent }}%"></span></div>
                    </div>
                    <div class="legend-item">
                        <div class="legend-item-top">
                            <span><i class="legend-dot red"></i>Absent</span>
                            <strong>{{ $absentToday }} · {{ $absentPercent }}%</strong>
                        </div>
                        <div class="legend-progress"><span class="red" style="width:{{ $absentPercent }}%"></span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="ed-card">
            <div class="card-head"><p class="card-title">Monthly Fee Collection ({{ now()->year }})</p><span class="view-link">This Year</span></div>
            <div class="bar-chart">
                @foreach($monthlyFee as $row)
                    <div class="bar-col"><div class="bar-line"><div class="bar-fill" style="height:{{ max(($row['value'] / $maxFee) * 100, 3) }}%"></div></div><div class="bar-label">{{ $row['label'] }}</div></div>
                @endforeach
            </div>
        </div>

        <div class="ed-card">
            <div class="card-head"><p class="card-title">Enquiries Trend ({{ now()->year }})</p><span class="view-link">This Year</span></div>
            <div class="line-chart">
                @foreach($monthlyEnquiries as $row)
                    <div class="line-point"><div class="line-stick" style="height:{{ max(($row['value'] / $maxEnquiry) * 130, 8) }}px"></div><div class="point-dot"></div><div class="line-label">{{ $row['label'] }}</div></div>
                @endforeach
            </div>
        </div>

        <div class="ed-card right-only">
            <div class="card-head"><p class="card-title">Latest Notices</p>@can('notice_access')<a class="view-link" href="{{ route('admin.notices.index') }}">View All</a>@endcan</div>
            <div class="notice-list">
                @forelse($latestNotices as $notice)
                    <div class="notice-item"><div class="mini-icon tone-orange"><i class="fas fa-bullhorn"></i></div><div><p class="item-title">{{ $notice->title }}</p><div class="item-meta">{{ $notice->publish_date ? \Carbon\Carbon::parse($notice->publish_date)->format('d M Y') : optional($notice->created_at)->format('d M Y') }}</div></div></div>
                @empty
                    <div class="item-meta">No notices found.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="wide-grid">
        <div class="ed-card">
            <div class="card-head"><p class="card-title">Exam Results Overview</p></div>
            <div class="score-box">
                <div class="item-meta">Average Percentage</div>
                <div class="exam-score">{{ $examAverage ?: 0 }}%</div>
                <div class="metric-trend"><i class="fas fa-arrow-up"></i> scoped exam result average</div>
                <div class="score-line"><div class="score-month"><div class="score-bar"></div><div class="bar-label">Jan</div></div><div class="score-month"><div class="score-bar" style="height:88px"></div><div class="bar-label">Feb</div></div><div class="score-month"><div class="score-bar" style="height:100px"></div><div class="bar-label">Mar</div></div></div>
            </div>
        </div>

        <div class="ed-card">
            <div class="card-head"><p class="card-title">Recent Admissions</p>@can('admission_access')<a class="view-link" href="{{ route('admin.admissions.index') }}">View All</a>@endcan</div>
            <table class="mini-table"><thead><tr><th>#</th><th>Student</th><th>Course</th><th>Batch</th><th>Date</th></tr></thead><tbody>
            @forelse($recentAdmissions as $admission)
                <tr><td>{{ $loop->iteration }}</td><td>{{ $admission->student->user->name ?? 'Student' }}</td><td>{{ $admission->course->name ?? '-' }}</td><td>{{ $admission->batch->name ?? '-' }}</td><td>{{ $admission->admission_date ? \Carbon\Carbon::parse($admission->admission_date)->format('d M') : '-' }}</td></tr>
            @empty
                <tr><td colspan="5">No admissions found.</td></tr>
            @endforelse
            </tbody></table>
        </div>

        <div class="ed-card">
            <div class="card-head"><p class="card-title">Recent Fee Payments</p>@can('fee_payment_access')<a class="view-link" href="{{ route('admin.fee-payments.index') }}">View All</a>@endcan</div>
            <table class="mini-table"><thead><tr><th>#</th><th>Student</th><th>Amount</th><th>Date</th><th>Receipt</th></tr></thead><tbody>
            @forelse($recentFeePayments as $payment)
                <tr><td>{{ $loop->iteration }}</td><td>{{ $payment->student->user->name ?? 'Student' }}</td><td>₹{{ number_format($payment->paid_amount,0) }}</td><td>{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d M') : '-' }}</td><td>{{ $payment->receipt_no ?? '-' }}</td></tr>
            @empty
                <tr><td colspan="5">No fee payments found.</td></tr>
            @endforelse
            </tbody></table>
        </div>
    </div>

    <div class="bottom-grid">
        <div class="ed-card">
            <div class="card-head"><p class="card-title">Today’s Classes / Timetable</p>@can('timetable_access')<a class="view-link" href="{{ route('admin.timetables.index') }}">View Full Timetable</a>@endcan</div>
            <table class="mini-table"><thead><tr><th>Time</th><th>Batch</th><th>Subject</th><th>Teacher</th><th>Room</th><th>Status</th></tr></thead><tbody>
            @forelse($todayClasses as $class)
                @php $isLive = now()->format('H:i:s') >= $class->start_time && now()->format('H:i:s') <= $class->end_time; @endphp
                <tr><td>{{ \Carbon\Carbon::parse($class->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($class->end_time)->format('h:i A') }}</td><td>{{ $class->batch->name ?? '-' }}</td><td>{{ $class->subject->name ?? '-' }}</td><td>{{ $class->teacher->user->name ?? '-' }}</td><td>{{ $class->room ?? '-' }}</td><td><span class="status-pill-sm {{ $isLive ? 'live' : 'upcoming' }}">{{ $isLive ? 'Live' : 'Upcoming' }}</span></td></tr>
            @empty
                <tr><td colspan="6">No classes found for today.</td></tr>
            @endforelse
            </tbody></table>
        </div>

        <div class="ed-card">
            <div class="card-head"><p class="card-title">Calendar</p></div>
            <div class="calendar">
                <div style="text-align:center;font-weight:900;margin-bottom:12px">{{ now()->format('F Y') }}</div>
                <div class="cal-grid">
                    @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day)<strong>{{ $day }}</strong>@endforeach
                    @for($i = 1; $i < $calendarStart->dayOfWeekIso; $i++)<span></span>@endfor
                    @for($day = 1; $day <= $daysInMonth; $day++)<div class="cal-day {{ $day == now()->day ? 'cal-active' : '' }}">{{ $day }}</div>@endfor
                </div>
            </div>
        </div>

        <div class="ed-card">
            <div class="card-head"><p class="card-title">Activity Feed</p></div>
            <div class="activity-list">
                @forelse($activityFeed as $activity)
                    <div class="activity-item"><div class="mini-icon tone-{{ $activity['tone'] }}"><i class="{{ $activity['icon'] }}"></i></div><div><p class="item-title">{{ $activity['title'] }}</p><div class="item-meta">{{ $activity['meta'] }}</div></div></div>
                @empty
                    <div class="item-meta">No activity found.</div>
                @endforelse
            </div>
        </div>
    </div>

    @if($scope['is_student'])
        <div class="ed-card portal-card" style="margin-top:14px">
            <div class="card-head"><p class="card-title"><i class="fas fa-graduation-cap"></i> Student Portal</p></div>
            <div class="portal-body">
                <div class="portal-meter"><strong>{{ $attendancePercent }}%</strong></div>
                <div class="portal-row"><span>Upcoming Exams</span><strong>{{ $upcomingExams->count() }}</strong></div>
                <div class="portal-row"><span>Pending Homework</span><strong>{{ $pendingHomeworkCount }}</strong></div>
                <div class="portal-row"><span>New Study Materials</span><strong>{{ $recentMaterials->count() }}</strong></div>
                <a href="{{ route('admin.my-portal.index') }}" class="portal-btn">Go to Student Portal</a>
            </div>
        </div>
    @endif
</div>
@endsection
