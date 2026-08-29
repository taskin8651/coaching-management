@extends('layouts.admin')

@section('page-title', 'My Portal')

@section('content')
@php
    $formatDate = fn ($date) => $date ? \Carbon\Carbon::parse($date)->format('d M Y') : '-';
    $formatTime = fn ($time) => $time ? \Carbon\Carbon::parse($time)->format('h:i A') : '-';
    $studentName = $portalStudent->user->name ?? 'Student';
    $welcomeName = $portalStudent->user->name ?? auth()->user()->name;
    $studentPhoto = $portalStudent->photo ?? null;
    $activeBatch = $portalStudent->batch ?? optional($portalStudent?->studentBatches?->firstWhere('status', 'active'))->batch;
    $materialCount = $studyMaterials->count();
    $subjectCount = $studentSubjectIds->count();
    $statusClass = function ($status) {
        return match ($status) {
            'present', 'paid', 'completed', 'pass', 'positive', 'submitted' => 'success',
            'late', 'partial', 'pending', 'neutral', 'active' => 'warning',
            'absent', 'overdue', 'fail', 'negative', 'incomplete' => 'danger',
            default => 'muted',
        };
    };
@endphp

<style>
    .portal-wrap{background:#f8fbff;margin:-10px -10px 0;padding:6px 4px 24px;color:#0f2344}
    .portal-hero{display:flex;align-items:center;justify-content:space-between;gap:16px;margin:8px 0 22px}
    .portal-title{display:flex;align-items:center;gap:14px}
    .portal-title-icon{width:42px;height:42px;border-radius:14px;display:grid;place-items:center;background:#eef5ff;color:#113b73;font-size:22px}
    .portal-title h2{font-size:28px;font-weight:800;margin:0;color:#0d1f3c}
    .portal-title p{margin:4px 0 0;color:#65758c;font-size:14px}
    .year-pill{border:1px solid #dce6f5;background:#fff;border-radius:10px;padding:12px 16px;color:#0d2a55;font-weight:700;box-shadow:0 8px 24px rgba(15,35,68,.04)}
    .portal-stat-grid{display:grid;grid-template-columns:repeat(5,minmax(150px,1fr));gap:14px;margin-bottom:22px}
    .portal-stat{background:#fff;border:1px solid #e4ebf5;border-radius:14px;padding:22px 18px;display:flex;align-items:center;gap:16px;box-shadow:0 12px 30px rgba(15,35,68,.05)}
    .portal-stat .icon{width:58px;height:58px;border-radius:12px;display:grid;place-items:center;color:#fff;font-size:24px;box-shadow:0 12px 24px rgba(28,91,199,.16)}
    .portal-stat.blue .icon{background:linear-gradient(135deg,#2f80ff,#4866ff)}
    .portal-stat.green .icon{background:linear-gradient(135deg,#23c66a,#08a853)}
    .portal-stat.purple .icon{background:linear-gradient(135deg,#a855f7,#6d5dfc)}
    .portal-stat.orange .icon{background:linear-gradient(135deg,#ff9d3d,#ff6b21)}
    .portal-stat.cyan .icon{background:linear-gradient(135deg,#13c5dd,#0295d4)}
    .portal-stat small{display:block;color:#0c2345;font-weight:700;margin-bottom:4px}
    .portal-stat strong{display:block;font-size:24px;line-height:1;color:#0d1f3c}
    .portal-stat span{display:block;margin-top:8px;font-size:12px;font-weight:700;color:#66758d}
    .portal-stat.green span,.portal-stat.cyan span{color:#04a85c}.portal-stat.purple span,.portal-stat.orange span{color:#f04438}
    .portal-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px}
    .portal-card{background:#fff;border:1px solid #e4ebf5;border-radius:14px;box-shadow:0 12px 30px rgba(15,35,68,.045);overflow:hidden}
    .portal-card-head{height:48px;padding:0 18px;border-bottom:1px solid #e8eef7;display:flex;align-items:center;justify-content:space-between}
    .portal-card-title{font-size:15px;font-weight:800;color:#0047bd;margin:0}
    .portal-link{font-size:12px;font-weight:800;color:#005bea;text-decoration:none}
    .portal-card-body{padding:18px}
    .overview-flex{display:flex;gap:24px;align-items:center}
    .student-photo{width:128px;height:128px;border-radius:50%;object-fit:cover;background:linear-gradient(135deg,#dbeafe,#bfdbfe);box-shadow:0 14px 35px rgba(13,64,125,.12)}
    .student-avatar{width:128px;height:128px;border-radius:50%;display:grid;place-items:center;background:linear-gradient(135deg,#e0ecff,#bcd5ff);font-size:46px;font-weight:900;color:#1d4ed8}
    .student-info h3{font-size:18px;font-weight:900;margin:0 0 10px;color:#0d1f3c}.student-badge{display:inline-flex;margin-left:8px;padding:5px 11px;border-radius:9px;background:#dcfce7;color:#15803d;font-size:12px;font-weight:800}
    .student-meta{display:grid;grid-template-columns:90px 1fr;gap:9px 12px;font-size:13px;color:#102a4e}.student-meta span{color:#5b6b82;font-weight:700}.student-mini-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:20px}
    .mini-card{border:1px solid #e6eef8;border-radius:10px;padding:16px 10px;text-align:center;background:linear-gradient(180deg,#fff,#fbfdff)}.mini-card small{display:block;color:#0050d8;font-weight:800;font-size:12px}.mini-card strong{display:block;margin-top:8px;color:#0047bd;font-size:24px}
    .notice-list,.material-list{display:flex;flex-direction:column}.notice-row,.material-row{display:grid;grid-template-columns:42px 1fr auto;gap:12px;align-items:center;padding:14px 0;border-bottom:1px solid #edf2f8}.notice-row:last-child,.material-row:last-child{border-bottom:0}.notice-icon{width:34px;height:34px;border-radius:50%;display:grid;place-items:center;background:#eef5ff;color:#1d5fd7}.notice-title,.file-title{font-weight:800;color:#0e2549;font-size:13px}.notice-desc,.file-meta{margin-top:4px;color:#64748b;font-size:12px}.notice-date{font-size:12px;color:#50627c;white-space:nowrap}
    .portal-table{width:100%;border-collapse:separate;border-spacing:0}.portal-table th{height:42px;background:#fbfdff;color:#12284b;font-size:12px;font-weight:900;text-align:left;border-bottom:1px solid #e8eef7;padding:0 14px}.portal-table td{padding:14px;border-bottom:1px solid #edf2f8;font-size:13px;color:#102a4e}.portal-table tr:last-child td{border-bottom:0}.portal-table-wrap{overflow:auto}.badge-soft{display:inline-flex;align-items:center;gap:5px;border-radius:999px;padding:5px 9px;font-size:12px;font-weight:800}.badge-soft.success{background:#dcfce7;color:#15803d}.badge-soft.warning{background:#fff2d8;color:#ea7600}.badge-soft.danger{background:#ffe4e8;color:#e11d48}.badge-soft.muted{background:#eef2f7;color:#64748b}
    .fee-summary{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin:0 10px 10px}.fee-box{border-radius:10px;border:1px solid #dfe9f7;background:#f8fbff;text-align:center;padding:13px}.fee-box.due{background:#fff5f5;border-color:#ffd8d8}.fee-box small{display:block;font-weight:800;color:#35506d}.fee-box strong{display:block;margin-top:8px;font-size:18px;color:#0d1f3c}.fee-box.due strong{color:#ef2626}
    .download-btn{width:34px;height:34px;border:1px solid #dbe6f5;border-radius:9px;display:grid;place-items:center;color:#005bea;background:#fff}.file-icon{width:34px;height:34px;border-radius:8px;display:grid;place-items:center;background:#ffeded;color:#ef2424}
    .timetable-table th,.timetable-table td{text-align:center;min-width:130px}.subject-chip{display:inline-flex;min-width:90px;justify-content:center;border-radius:8px;padding:8px 10px;font-weight:900;font-size:12px;background:#edf7ff;color:#0069d9}.subject-chip:nth-child(3n){background:#f3eefe;color:#7c3aed}.empty-state{padding:46px 20px;text-align:center;color:#64748b}.empty-state i{font-size:34px;color:#a9bad0;margin-bottom:10px}.empty-line{color:#9aa8ba;font-size:13px}
    @media(max-width:1200px){.portal-stat-grid{grid-template-columns:repeat(2,1fr)}.portal-grid{grid-template-columns:1fr}}@media(max-width:680px){.portal-hero,.overview-flex{align-items:flex-start;flex-direction:column}.portal-stat-grid,.student-mini-grid,.fee-summary{grid-template-columns:1fr}.portal-wrap{margin:0;padding:0}.year-pill{width:100%}}
</style>

<div class="portal-wrap">
    <div class="portal-hero">
        <div class="portal-title">
            <div class="portal-title-icon"><i class="fas fa-user"></i></div>
            <div>
                <h2>My Portal</h2>
                <p>Welcome back, {{ $welcomeName }}! Here's what's happening.</p>
            </div>
        </div>
        <div class="year-pill">Academic Year {{ now('Asia/Kolkata')->format('Y') }}-{{ now('Asia/Kolkata')->addYear()->format('y') }} <i class="fas fa-chevron-down ml-2"></i></div>
    </div>

    @if($scope['is_parent'] && $children->count() > 1)
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin:-10px 0 20px;">
            @foreach($children as $child)
                <a href="{{ route('admin.my-portal.index', ['student_id' => $child->id]) }}"
                   style="padding:8px 16px;border-radius:10px;font-size:13px;font-weight:800;text-decoration:none;border:1px solid #dce6f5;{{ $portalStudent && $portalStudent->id == $child->id ? 'background:#0047bd;color:#fff;border-color:#0047bd;' : 'background:#fff;color:#0d2a55;' }}">
                    {{ $child->user->name ?? 'Child #' . $child->id }}
                </a>
            @endforeach
        </div>
    @endif

    @if(! $portalStudent && $scope['is_teacher'])
        <div class="portal-stat-grid">
            @foreach($teacherStats as $stat)
                <div class="portal-stat {{ $stat['color'] }}">
                    <div class="icon"><i class="fas {{ $stat['icon'] }}"></i></div>
                    <div>
                        <small>{{ $stat['label'] }}</small>
                        <strong>{{ $stat['value'] }}</strong>
                        <span>{{ $stat['note'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="portal-card mb-3">
            <div class="portal-card-head"><p class="portal-card-title">My Timetable</p></div>
            <div class="portal-table-wrap">
                <table class="portal-table">
                    <thead><tr><th>Day</th><th>Time</th><th>Subject</th><th>Batch</th><th>Room</th></tr></thead>
                    <tbody>
                        @forelse($teacherTimetables as $row)
                            <tr>
                                <td>{{ $row->day_of_week ?? '-' }}</td>
                                <td>{{ $formatTime($row->start_time) }} - {{ $formatTime($row->end_time) }}</td>
                                <td>{{ $row->subject->name ?? '-' }}</td>
                                <td>{{ $row->batch->name ?? '-' }}</td>
                                <td>{{ $row->room ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty-line">No timetable assigned.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($upcomingHolidays->isNotEmpty())
            <div class="portal-card mb-3">
                <div class="portal-card-head"><p class="portal-card-title">Upcoming Holidays</p></div>
                <div class="notice-list" style="padding:6px 18px;">
                    @foreach($upcomingHolidays as $holiday)
                        <div class="notice-row">
                            <div class="notice-icon"><i class="fas fa-umbrella-beach"></i></div>
                            <div>
                                <div class="notice-title">{{ $holiday->name }}</div>
                                <div class="notice-desc">{{ $holiday->branch_id ? 'Branch Holiday' : 'Institute-wide' }} · {{ ucfirst($holiday->type) }}</div>
                            </div>
                            <div class="notice-date">{{ $formatDate($holiday->date) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="portal-grid">
            <div class="portal-card">
                <div class="portal-card-head"><p class="portal-card-title">Faculty Log Book</p></div>
                <div class="portal-table-wrap">
                    <table class="portal-table">
                        <thead><tr><th>Date</th><th>Batch</th><th>Subject</th><th>Scheduled</th><th>Actual</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($facultyLogs->take(8) as $row)
                                <tr>
                                    <td>{{ $formatDate($row->lecture_date) }}</td>
                                    <td>{{ $row->batch->name ?? '-' }}</td>
                                    <td>{{ $row->subject->name ?? '-' }}</td>
                                    <td>{{ $formatTime($row->scheduled_start_time) }} - {{ $formatTime($row->scheduled_end_time) }}</td>
                                    <td>{{ $formatTime($row->actual_start_time) }} - {{ $formatTime($row->actual_end_time) }}</td>
                                    <td><span class="badge-soft {{ $statusClass($row->approval_status) }}">{{ ucfirst($row->approval_status ?? '-') }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="empty-line">No faculty log entries found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="portal-card">
                <div class="portal-card-head"><p class="portal-card-title">Salary Payments</p></div>
                <div class="portal-table-wrap">
                    <table class="portal-table">
                        <thead><tr><th>Month</th><th>Net Salary</th><th>Paid</th><th>Due</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($salaryPayments as $row)
                                <tr>
                                    <td>{{ $row->salary_month }}</td>
                                    <td>₹{{ number_format($row->net_salary, 0) }}</td>
                                    <td>₹{{ number_format($row->paid_amount, 0) }}</td>
                                    <td>₹{{ number_format($row->due_amount, 0) }}</td>
                                    <td><span class="badge-soft {{ $statusClass($row->payment_status) }}">{{ ucfirst($row->payment_status ?? '-') }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="empty-line">No salary payment records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @elseif(! $portalStudent)
        <div class="portal-card">
            <div class="empty-state">
                <i class="fas fa-user-graduate"></i>
                <h3>No student profile found for this login.</h3>
                <p>My Portal student login ya parent login ke liye personal academic dashboard show karta hai.</p>
            </div>
        </div>
    @else
        <div class="portal-stat-grid">
            @foreach($portalStats as $stat)
                <div class="portal-stat {{ $stat['color'] }}">
                    <div class="icon"><i class="fas {{ $stat['icon'] }}"></i></div>
                    <div>
                        <small>{{ $stat['label'] }}</small>
                        <strong>{{ $stat['value'] }}</strong>
                        <span>{{ $stat['note'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="portal-grid">
            <div class="portal-card">
                <div class="portal-card-head"><p class="portal-card-title">Student Overview</p></div>
                <div class="portal-card-body">
                    <div class="overview-flex">
                        @if($studentPhoto)
                            <img src="{{ $studentPhoto }}" class="student-photo" alt="{{ $studentName }}">
                        @else
                            <div class="student-avatar">{{ strtoupper(substr($studentName, 0, 1)) }}</div>
                        @endif
                        <div class="student-info">
                            <h3>{{ $studentName }} <span class="student-badge">{{ ucfirst($portalStudent->status ?? 'Student') }}</span></h3>
                            <div class="student-meta">
                                <span>Roll No:</span><strong>{{ $portalStudent->student_code ?? '-' }}</strong>
                                <span>Batch:</span><strong>{{ $activeBatch->name ?? '-' }}</strong>
                                <span>Course:</span><strong>{{ $portalStudent->course->name ?? '-' }}</strong>
                                <span>Phone:</span><strong>{{ $portalStudent->phone ?? $portalStudent->user->phone ?? '-' }}</strong>
                                <span>Email:</span><strong>{{ $portalStudent->user->email ?? '-' }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="student-mini-grid">
                        <div class="mini-card"><small>Total Subjects</small><strong>{{ $subjectCount }}</strong></div>
                        <div class="mini-card"><small>Teachers</small><strong>{{ $teacherCount }}</strong></div>
                        <div class="mini-card"><small>Classes / Week</small><strong>{{ $classesPerWeek }}</strong></div>
                        <div class="mini-card"><small>Study Materials</small><strong>{{ $materialCount }}</strong></div>
                    </div>
                </div>
            </div>

            <div class="portal-card">
                <div class="portal-card-head">
                    <p class="portal-card-title">Recent Notices</p>
                    @if(Route::has('admin.notices.index'))<a class="portal-link" href="{{ route('admin.notices.index') }}">View All</a>@endif
                </div>
                <div class="portal-card-body">
                    <div class="notice-list">
                        @forelse($recentNotices as $notice)
                            <div class="notice-row">
                                <div class="notice-icon"><i class="fas fa-bullhorn"></i></div>
                                <div>
                                    <div class="notice-title">{{ $notice->title }}</div>
                                    <div class="notice-desc">{{ \Illuminate\Support\Str::limit(strip_tags($notice->description), 70) }}</div>
                                </div>
                                <div class="notice-date">{{ $formatDate($notice->publish_date) }}</div>
                            </div>
                        @empty
                            <p class="empty-line">No recent notices.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        @if($upcomingHolidays->isNotEmpty())
            <div class="portal-card mb-3">
                <div class="portal-card-head"><p class="portal-card-title">Upcoming Holidays</p></div>
                <div class="notice-list" style="padding:6px 18px;">
                    @foreach($upcomingHolidays as $holiday)
                        <div class="notice-row">
                            <div class="notice-icon"><i class="fas fa-umbrella-beach"></i></div>
                            <div>
                                <div class="notice-title">{{ $holiday->name }}</div>
                                <div class="notice-desc">{{ $holiday->branch_id ? 'Branch Holiday' : 'Institute-wide' }} · {{ ucfirst($holiday->type) }}</div>
                            </div>
                            <div class="notice-date">{{ $formatDate($holiday->date) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="portal-grid">
            <div class="portal-card">
                <div class="portal-card-head"><p class="portal-card-title">Attendance Summary</p></div>
                <div class="portal-table-wrap">
                    <table class="portal-table">
                        <thead><tr><th>Date</th><th>Subject</th><th>Batch</th><th>Status</th><th>In Time</th><th>Out Time</th></tr></thead>
                        <tbody>
                            @forelse($studentAttendances->take(5) as $row)
                                <tr>
                                    <td>{{ $formatDate($row->attendance_date) }}</td>
                                    <td>{{ $row->subject->name ?? '-' }}</td>
                                    <td>{{ $row->batch->name ?? '-' }}</td>
                                    <td><span class="badge-soft {{ $statusClass($row->status) }}">● {{ ucfirst($row->status ?? '-') }}</span></td>
                                    <td>{{ $formatTime($row->actual_in_time) }}</td>
                                    <td>{{ $formatTime($row->actual_out_time) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="empty-line">No attendance records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="portal-card">
                <div class="portal-card-head"><p class="portal-card-title">Homework</p></div>
                <div class="portal-table-wrap">
                    <table class="portal-table">
                        <thead><tr><th>Title</th><th>Subject</th><th>Due Date</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($homeworks->take(5) as $row)
                                @php
                                    $submission = $row->submissions->firstWhere('student_id', $portalStudent->id);
                                    $hwStatus = $submission->status ?? ($row->status === 'closed' ? 'completed' : 'pending');
                                @endphp
                                <tr>
                                    <td><strong>{{ $row->title }}</strong></td>
                                    <td>{{ $row->subject->name ?? '-' }}</td>
                                    <td>{{ $formatDate($row->due_date) }}</td>
                                    <td><span class="badge-soft {{ $statusClass($hwStatus) }}">{{ ucfirst($hwStatus) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="empty-line">No homework assigned.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="portal-grid">
            <div class="portal-card">
                <div class="portal-card-head"><p class="portal-card-title">Fee Details</p></div>
                <div class="portal-card-body" style="padding:10px 8px 0">
                    <div class="fee-summary">
                        <div class="fee-box"><small>Total Fees</small><strong>₹{{ number_format($feeTotal, 0) }}</strong></div>
                        <div class="fee-box"><small>Paid Amount</small><strong>₹{{ number_format($feePaid, 0) }}</strong></div>
                        <div class="fee-box due"><small>Due Amount</small><strong>₹{{ number_format($feeDue, 0) }}</strong></div>
                    </div>
                </div>
                <div class="portal-table-wrap">
                    <table class="portal-table">
                        <thead><tr><th>Installment</th><th>Due Date</th><th>Amount</th><th>Paid</th><th>Due</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($feeInstallments->take(5) as $row)
                                <tr>
                                    <td>{{ $row->title }}</td>
                                    <td>{{ $formatDate($row->due_date) }}</td>
                                    <td>₹{{ number_format($row->amount, 0) }}</td>
                                    <td>₹{{ number_format($row->paid_amount, 0) }}</td>
                                    <td>₹{{ number_format($row->due_amount, 0) }}</td>
                                    <td><span class="badge-soft {{ $statusClass($row->status) }}">{{ ucfirst($row->status ?? '-') }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="empty-line">No fee installments found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="portal-card">
                <div class="portal-card-head"><p class="portal-card-title">Exam Results</p></div>
                <div class="portal-table-wrap">
                    <table class="portal-table">
                        <thead><tr><th>Exam</th><th>Date</th><th>Total Marks</th><th>Obtained</th><th>%</th><th>Grade</th></tr></thead>
                        <tbody>
                            @forelse($reportCards->take(5) as $row)
                                <tr>
                                    <td>{{ $row->exam->title ?? 'Exam' }}</td>
                                    <td>{{ $formatDate($row->exam->exam_date ?? $row->published_at) }}</td>
                                    <td>{{ number_format($row->total_marks, 0) }}</td>
                                    <td>{{ number_format($row->marks_obtained, 0) }}</td>
                                    <td>{{ number_format($row->percentage, 1) }}%</td>
                                    <td><strong>{{ $row->grade ?? '-' }}</strong></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="empty-line">No published results found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="portal-grid">
            <div class="portal-card">
                <div class="portal-card-head"><p class="portal-card-title">Study Materials</p></div>
                <div class="portal-card-body">
                    <div class="material-list">
                        @forelse($studyMaterials->take(5) as $row)
                            @php $file = collect($row->files ?? [])->first(); @endphp
                            <div class="material-row">
                                <div class="file-icon"><i class="fas fa-file-pdf"></i></div>
                                <div>
                                    <div class="file-title">{{ $row->title }}</div>
                                    <div class="file-meta">{{ $row->subject->name ?? $row->material_type ?? 'Material' }} · {{ $formatDate($row->created_at) }}</div>
                                </div>
                                @if($file && !empty($file['url']))
                                    <a class="download-btn" href="{{ $file['url'] }}" target="_blank"><i class="fas fa-download"></i></a>
                                @elseif($row->external_link)
                                    <a class="download-btn" href="{{ $row->external_link }}" target="_blank"><i class="fas fa-link"></i></a>
                                @else
                                    <span class="download-btn"><i class="fas fa-file"></i></span>
                                @endif
                            </div>
                        @empty
                            <p class="empty-line">No study materials found.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="portal-card">
                <div class="portal-card-head"><p class="portal-card-title">Teacher Remarks</p></div>
                <div class="portal-table-wrap">
                    <table class="portal-table">
                        <thead><tr><th>Date</th><th>Teacher</th><th>Remark</th><th>Type</th></tr></thead>
                        <tbody>
                            @forelse($remarks->take(5) as $row)
                                <tr>
                                    <td>{{ $formatDate($row->remark_date) }}</td>
                                    <td>{{ $row->teacher->user->name ?? '-' }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($row->remark, 48) }}</td>
                                    <td><span class="badge-soft {{ $statusClass($row->remark_type) }}">{{ ucfirst($row->remark_type ?? '-') }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="empty-line">No teacher remarks found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="portal-card">
            <div class="portal-card-head"><p class="portal-card-title">My Timetable</p></div>
            <div class="portal-table-wrap">
                <table class="portal-table timetable-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            @foreach($weekDays as $day)<th>{{ $day }}</th>@endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($timetableRows as $row)
                            <tr>
                                <td><strong>{{ $row['slot'] }}</strong></td>
                                @foreach($weekDays as $day)
                                    @php $class = $row['days'][$day] ?? null; @endphp
                                    <td>
                                        @if($class)
                                            <span class="subject-chip">{{ $class->subject->name ?? 'Class' }}</span>
                                            <div class="empty-line">{{ $class->batch->name ?? '' }}</div>
                                        @else
                                            <span class="empty-line">-</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($weekDays) + 1 }}" class="empty-line">No timetable found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
