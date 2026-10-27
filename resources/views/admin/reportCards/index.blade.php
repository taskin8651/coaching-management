@extends('layouts.admin')

@section('page-title', 'Report Cards')

@section('styles')
<style>
    .rc-metric-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:16px}
    .rc-metric-card{background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:16px 18px;box-shadow:0 10px 28px rgba(15,23,42,.045);display:flex;gap:13px;align-items:center}
    .rc-metric-icon{width:48px;height:48px;border-radius:14px;display:grid;place-items:center;font-size:19px;flex:0 0 auto}
    .rc-tone-blue{background:#dbeafe;color:#2563eb}
    .rc-tone-green{background:#dcfce7;color:#16a34a}
    .rc-tone-purple{background:#f3e8ff;color:#9333ea}
    .rc-tone-amber{background:#fef3c7;color:#d97706}
    .rc-metric-value{font-size:22px;font-weight:900;margin:0;color:#0f172a;line-height:1.15}
    .rc-metric-label{font-size:11.5px;color:#64748b;font-weight:700;margin:4px 0 0;text-transform:uppercase;letter-spacing:.03em}
    .rc-metric-sub{font-size:11px;color:#94a3b8;margin:3px 0 0}

    @media(max-width:1100px){.rc-metric-grid{grid-template-columns:repeat(2,1fr)}}
    @media(max-width:600px){.rc-metric-grid{grid-template-columns:1fr}}

    @media print {
        #sidebar, #main-header, #sidebar-overlay, .no-print { display: none !important; }
        #main-content { margin: 0 !important; padding: 0 !important; }
        #rcListView { display: block !important; margin-bottom: 16px; }
        .admin-content { padding: 0 !important; }
    }
</style>
@endsection

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Report Cards</h2>
        <p class="admin-page-subtitle">
            @if($isStudent)
                Your published report cards
            @else
                Generated result summaries, parent visibility and publish status
            @endif
        </p>
    </div>
    @if($isStudent)
        <a href="{{ route('admin.report-cards.preview') }}" class="btn-primary">
            <i class="fas fa-eye"></i> Live report preview
        </a>
    @endif
</div>

@if(!$isStudent)
<div class="rc-metric-grid">
    <div class="rc-metric-card">
        <div class="rc-metric-icon rc-tone-blue"><i class="fas fa-file-alt"></i></div>
        <div>
            <p class="rc-metric-value">{{ $summary['total'] }}</p>
            <p class="rc-metric-label">Total Report Cards</p>
        </div>
    </div>

    <div class="rc-metric-card">
        <div class="rc-metric-icon rc-tone-purple"><i class="fas fa-paper-plane"></i></div>
        <div>
            <p class="rc-metric-value">{{ $reportCards->where('published_to_parent', 1)->count() }}</p>
            <p class="rc-metric-label">Published to Parent</p>
        </div>
    </div>

    <div class="rc-metric-card">
        <div class="rc-metric-icon rc-tone-amber"><i class="fas fa-percentage"></i></div>
        <div>
            <p class="rc-metric-value">{{ $summary['average_percentage'] }}%</p>
            <p class="rc-metric-label">Average Percentage</p>
        </div>
    </div>

    <div class="rc-metric-card">
        <div class="rc-metric-icon rc-tone-green"><i class="fas fa-check-circle"></i></div>
        <div>
            <p class="rc-metric-value">{{ $summary['pass_percentage'] }}%</p>
            <p class="rc-metric-label">Pass Rate</p>
            <p class="rc-metric-sub">{{ $summary['pass_count'] }} passed · {{ $summary['fail_count'] }} failed</p>
        </div>
    </div>
</div>
@endif

<div class="page-card no-print" style="margin-bottom:16px;padding:18px;">
    <h3 class="page-card-title">Live report preview</h3>
    <p class="page-card-note">
        @if($isStudent)
            Preview your latest saved test results. You can filter by date and batch.
        @else
            Preview latest saved marks and test remarks. Choose a student for an individual report, or leave blank for all eligible students.
        @endif
    </p>
    <form method="GET" action="{{ route('admin.report-cards.preview') }}" class="admin-form-grid" style="align-items:end;">
        <div class="field-group mb-0"><label class="field-label" for="preview_student_id">Student</label>
            <select class="field-input" id="preview_student_id" name="student_id">
                @unless($isStudent)<option value="">All students</option>@endunless
                @foreach($students as $id => $name)
                    <option value="{{ $id }}" {{ $isStudent || request('student_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="field-group mb-0"><label class="field-label" for="preview_from">From</label><input class="field-input" type="date" id="preview_from" name="date_from"></div>
        <div class="field-group mb-0"><label class="field-label" for="preview_to">To</label><input class="field-input" type="date" id="preview_to" name="date_to"></div>
        <div class="field-group mb-0"><label class="field-label" for="preview_batch">Batch</label><select class="field-input" id="preview_batch" name="batch_id">@foreach($batches as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select></div>
        <button class="btn-primary" type="submit"><i class="fas fa-eye"></i> Preview Report</button>
    </form>
</div>
<div id="rcListView">
<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">{{ $isStudent ? 'My Report Cards' : 'All Report Cards' }}</p>

        <span class="page-card-note">
            <i class="fas fa-eye"></i>
            @if($isStudent) Open any row to view your complete live report @else Publish report cards for parent visibility @endif
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-ReportCards">
            <thead>
                <tr>
                        @unless($isStudent)<th>Student</th>@endunless
                    <th>Exam</th>
                    <th>Batch</th>
                    <th>Subject / Course</th>
                    <th>Marks</th>
                    <th>Percentage</th>
                    <th>Grade</th>
                    <th>Result</th>
                    <th>Rank</th>
                    @unless($isStudent)
                        <th style="text-align:right;" class="no-print">Publish</th>
                    @endunless
                    <th style="text-align:right;" class="no-print">Full Report</th>
                </tr>
            </thead>

            <tbody>
                @foreach($reportCards as $card)
                    <tr>
                        @unless($isStudent)
                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $name = $card->student->user->name ?? 'Student';
                                    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                                    $color = $colors[$loop->index % count($colors)];
                                @endphp

                                <div class="avatar-circle" style="background: {{ $color }};">
                                    {{ strtoupper(substr($name, 0, 1)) }}
                                </div>

                                <div>
                                    <p class="table-main-text">{{ $name }}</p>
                                    <p class="table-sub-text">{{ $card->student->student_code ?? 'Student' }}</p>
                                </div>
                            </div>
                        </td>
                        @endunless

                        <td>
                            <p class="table-main-text">{{ $card->exam->title ?? '-' }}</p>
                            <p class="table-sub-text">
                                {{ $card->exam->exam_type ?? 'Exam' }}
                                @if($card->exam && $card->exam->exam_date)
                                    · {{ $card->exam->exam_date->format('d M Y') }}
                                @endif
                            </p>
                        </td>

                        <td>{{ $card->exam->batch->name ?? $card->batch->name ?? '-' }}</td>

                        <td>
                            <p class="table-main-text">{{ $card->exam->subject->name ?? '-' }}</p>
                            <p class="table-sub-text">{{ $card->exam->course->name ?? '-' }}</p>
                        </td>

                        <td>
                            <strong>{{ $card->marks_obtained ?? 0 }}</strong>
                            /
                            {{ $card->total_marks ?? 0 }}
                        </td>

                        <td>
                            <span class="code-pill">
                                {{ number_format($card->percentage ?? 0, 2) }}%
                            </span>
                        </td>

                        <td>
                            @if($card->grade)
                                <span class="status-pill success">{{ $card->grade }}</span>
                            @else
                                <span style="font-size:12px;color:#94A3B8;">—</span>
                            @endif
                        </td>

                        <td>
                            @if($card->exam && $card->exam->passing_marks !== null)
                                @if($card->marks_obtained >= $card->exam->passing_marks)
                                    <span class="status-pill success">Pass</span>
                                @else
                                    <span class="status-pill danger">Fail</span>
                                @endif
                            @else
                                <span style="font-size:12px;color:#94A3B8;">—</span>
                            @endif
                        </td>

                        <td>
                            @if($card->rank)
                                <span class="code-pill">#{{ $card->rank }}</span>
                            @else
                                <span style="font-size:12px;color:#94A3B8;">—</span>
                            @endif
                        </td>

                        @unless($isStudent)
                        <td class="no-print">
                            <div class="action-row">
                                @can('report_card_publish')
                                    @if(!$card->published_to_parent)
                                        <form method="POST"
                                              action="{{ route('admin.report-cards.publish', $card->id) }}"
                                              style="display:inline;">
                                            @csrf

                                            <button type="submit" class="btn-outline">
                                                <i class="fas fa-paper-plane"></i>
                                                Publish
                                            </button>
                                        </form>
                                    @else
                                        <span class="status-pill success">
                                            <i class="fas fa-check"></i>
                                            Done
                                        </span>
                                    @endif
                                @else
                                    <span style="font-size:12px;color:#94A3B8;">—</span>
                                @endcan
                            </div>
                        </td>
                        @endunless
                        <td class="no-print">
                            <a class="btn-outline" href="{{ route('admin.report-cards.preview', ['student_id' => $card->student_id]) }}">
                                <i class="fas fa-file-alt"></i> View full report
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</div>

@endsection

@section('scripts')
@parent
<script>
$(function () {
    initAdminDataTable('.datatable-ReportCards', {
        searchPlaceholder: 'Search report cards...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ report cards'
    });
});
</script>
@endsection
