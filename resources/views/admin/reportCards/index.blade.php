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

    .rc-chart-grid{display:grid;grid-template-columns:1fr 1.4fr;gap:14px;margin-bottom:16px}
    .rc-chart-card{background:#fff;border:1px solid #e5e7eb;border-radius:18px;box-shadow:0 10px 28px rgba(15,23,42,.045);overflow:hidden}
    .rc-chart-head{display:flex;justify-content:space-between;align-items:center;padding:16px 18px;border-bottom:1px solid #eef2f7}
    .rc-chart-title{font-size:14px;font-weight:900;margin:0;color:#0f172a}
    .rc-chart-body{padding:18px;min-height:230px;display:flex;align-items:center;justify-content:center}
    .rc-empty{font-size:13px;color:#94a3b8;text-align:center;padding:30px 0}

    .rc-legend{display:flex;flex-wrap:wrap;gap:10px;padding:0 18px 18px}
    .rc-legend-item{display:flex;align-items:center;gap:7px;font-size:12.5px;color:#334155;font-weight:700}
    .rc-legend-dot{width:10px;height:10px;border-radius:3px;display:inline-block}

    @media(max-width:1100px){.rc-metric-grid{grid-template-columns:repeat(2,1fr)}.rc-chart-grid{grid-template-columns:1fr}}
    @media(max-width:600px){.rc-metric-grid{grid-template-columns:1fr}}

    @media print {
        #sidebar, #main-header, #sidebar-overlay, .no-print { display: none !important; }
        #main-content { margin: 0 !important; padding: 0 !important; }
        #rcListView, #rcGraphView { display: block !important; margin-bottom: 16px; }
        .admin-content { padding: 0 !important; }
    }
</style>
@endsection

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Report Cards</h2>
        <p class="admin-page-subtitle">
            Generated result summaries, parent visibility and publish status
        </p>
    </div>

    <div class="action-row no-print" style="gap:10px;">
        <button type="button" id="rcViewToggleBtn" class="btn-outline">
            <i class="fas fa-chart-pie"></i>
            Show Graph
        </button>

        <a href="{{ route('admin.report-cards.export') }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}"
           class="btn-primary">
            <i class="fas fa-file-excel"></i>
            Export Excel (List + Graph)
        </a>
    </div>
</div>

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

<div class="page-card no-print" style="margin-bottom:16px;">
    <div class="page-card-header">
        <p class="page-card-title">Filters</p>

        <span class="page-card-note">
            <i class="fas fa-filter"></i>
            Student / Teacher / Subject / Course / Date wise report dekhein
        </span>
    </div>

    <form method="GET" action="{{ route('admin.report-cards.index') }}" style="padding:16px;">
        <div class="admin-form-grid" style="grid-template-columns: repeat(5, 1fr); gap:12px;">

            <div class="field-group mb-0">
                <label class="field-label" for="filter_student_id">Student</label>
                <select name="student_id" id="filter_student_id" class="field-input">
                    @foreach($students as $id => $name)
                        <option value="{{ $id }}" {{ ($filters['student_id'] ?? '') == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field-group mb-0">
                <label class="field-label" for="filter_teacher_id">Teacher</label>
                <select name="teacher_id" id="filter_teacher_id" class="field-input">
                    @foreach($teachers as $id => $name)
                        <option value="{{ $id }}" {{ ($filters['teacher_id'] ?? '') == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field-group mb-0">
                <label class="field-label" for="filter_subject_id">Subject</label>
                <select name="subject_id" id="filter_subject_id" class="field-input">
                    @foreach($subjects as $id => $name)
                        <option value="{{ $id }}" {{ ($filters['subject_id'] ?? '') == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field-group mb-0">
                <label class="field-label" for="filter_course_id">Course</label>
                <select name="course_id" id="filter_course_id" class="field-input">
                    @foreach($courses as $id => $name)
                        <option value="{{ $id }}" {{ ($filters['course_id'] ?? '') == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field-group mb-0">
                <label class="field-label">Exam Date</label>
                <div style="display:flex; gap:6px;">
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="field-input">
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="field-input">
                </div>
            </div>

        </div>

        <div class="action-row" style="justify-content:flex-start; gap:10px; margin-top:14px;">
            <button type="submit" class="btn-primary">
                <i class="fas fa-filter"></i>
                Apply Filters
            </button>

            <a href="{{ route('admin.report-cards.index') }}" class="btn-ghost">
                Clear
            </a>
        </div>
    </form>
</div>

@can('report_card_create')
    <div class="page-card no-print" style="margin-bottom:16px;">
        <div class="page-card-header">
            <p class="page-card-title">Generate Report Cards</p>

            <span class="page-card-note">
                <i class="fas fa-file-alt"></i>
                Select an exam (with results entered) to generate report cards
            </span>
        </div>

        <div style="padding:16px;">
            <form method="POST" action="{{ route('admin.report-cards.generate') }}">
                @csrf

                <div class="action-row" style="justify-content:flex-start; gap:12px;">
                    <div class="input-icon-wrap" style="max-width:420px;">
                        <i class="fas fa-clipboard-list icon"></i>

                        <select name="exam_id"
                                required
                                class="field-input {{ $errors->has('exam_id') ? 'error' : '' }}">
                            @foreach($exams as $id => $label)
                                <option value="{{ $id }}" {{ old('exam_id') == $id ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class="fas fa-sync-alt"></i>
                        Generate
                    </button>
                </div>

                @if($errors->has('exam_id'))
                    <p class="field-error" style="margin-top:8px;">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $errors->first('exam_id') }}
                    </p>
                @elseif($exams->count() <= 1)
                    <p class="field-hint" style="margin-top:8px;">
                        <i class="fas fa-info-circle"></i>
                        No completed exam with results found yet. Enter results for an exam first.
                    </p>
                @endif
            </form>
        </div>
    </div>
@endcan

<div id="rcGraphView" style="display:none;">
    <div class="rc-chart-grid">
        <div class="rc-chart-card">
            <div class="rc-chart-head">
                <p class="rc-chart-title">Pass vs Fail</p>
                <span class="page-card-note"><i class="fas fa-chart-pie"></i> by passing marks</span>
            </div>

            <div class="rc-chart-body">
                @if($summary['pass_count'] + $summary['fail_count'] > 0)
                    <canvas id="rcPassFailChart" height="200"></canvas>
                @else
                    <p class="rc-empty">Result data not available yet for the selected filters.</p>
                @endif
            </div>
        </div>

        <div class="rc-chart-card">
            <div class="rc-chart-head">
                <p class="rc-chart-title">Grade Distribution</p>
                <span class="page-card-note"><i class="fas fa-chart-bar"></i> across filtered report cards</span>
            </div>

            <div class="rc-chart-body">
                @if($summary['total'])
                    <canvas id="rcGradeChart" height="200"></canvas>
                @else
                    <p class="rc-empty">No report cards found for the selected filters.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div id="rcListView">
<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Report Cards</p>

        <span class="page-card-note">
            <i class="fas fa-eye"></i>
            Publish report cards for parent visibility
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-ReportCards">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Exam</th>
                    <th>Subject / Course</th>
                    <th>Marks</th>
                    <th>Percentage</th>
                    <th>Grade</th>
                    <th>Result</th>
                    <th>Rank</th>
                    <th>Parent Status</th>
                    <th style="text-align:right;" class="no-print">Publish</th>
                </tr>
            </thead>

            <tbody>
                @foreach($reportCards as $card)
                    <tr>
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

                        <td>
                            <p class="table-main-text">{{ $card->exam->title ?? '-' }}</p>
                            <p class="table-sub-text">
                                {{ $card->exam->exam_type ?? 'Exam' }}
                                @if($card->exam && $card->exam->exam_date)
                                    · {{ $card->exam->exam_date->format('d M Y') }}
                                @endif
                            </p>
                        </td>

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

                        <td>
                            @if($card->published_to_parent)
                                <span class="status-pill success">Published</span>
                            @else
                                <span class="status-pill warning">Draft</span>
                            @endif
                        </td>

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
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
$(function () {
    initAdminDataTable('.datatable-ReportCards', {
        searchPlaceholder: 'Search report cards...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ report cards'
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const listView = document.getElementById('rcListView');
    const graphView = document.getElementById('rcGraphView');
    const toggleBtn = document.getElementById('rcViewToggleBtn');

    let chartsReady = false;

    function initCharts() {
        if (chartsReady || typeof Chart === 'undefined') {
            return;
        }

        chartsReady = true;

        const passCount = {{ $summary['pass_count'] }};
        const failCount = {{ $summary['fail_count'] }};
        const passFailCanvas = document.getElementById('rcPassFailChart');

        if (passFailCanvas && (passCount + failCount) > 0) {
            new Chart(passFailCanvas, {
                type: 'doughnut',
                data: {
                    labels: ['Pass', 'Fail'],
                    datasets: [{
                        data: [passCount, failCount],
                        backgroundColor: ['#16a34a', '#ef4444'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    cutout: '68%',
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 12, weight: '600' } } }
                    }
                }
            });
        }

        const gradeLabels = @json($summary['grade_counts']->keys());
        const gradeValues = @json($summary['grade_counts']->values());
        const gradeCanvas = document.getElementById('rcGradeChart');

        if (gradeCanvas && gradeLabels.length) {
            new Chart(gradeCanvas, {
                type: 'bar',
                data: {
                    labels: gradeLabels,
                    datasets: [{
                        label: 'Report Cards',
                        data: gradeValues,
                        backgroundColor: '#2563eb',
                        borderRadius: 6,
                        maxBarThickness: 46,
                    }]
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        }
    }

    const VIEW_STORAGE_KEY = 'reportCards.view';

    function showGraphView() {
        if (!listView || !graphView) return;
        listView.style.display = 'none';
        graphView.style.display = '';
        initCharts();
        toggleBtn.innerHTML = '<i class="fas fa-list"></i> Show List';
        localStorage.setItem(VIEW_STORAGE_KEY, 'graph');
    }

    function showListView() {
        if (!listView || !graphView) return;
        graphView.style.display = 'none';
        listView.style.display = '';
        toggleBtn.innerHTML = '<i class="fas fa-chart-pie"></i> Show Graph';
        localStorage.setItem(VIEW_STORAGE_KEY, 'list');
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            const isGraphShown = graphView.style.display !== 'none';
            isGraphShown ? showListView() : showGraphView();
        });
    }

    // Filters submit as a full page reload — keep showing whichever view (list/graph)
    // was active before the filter was applied instead of always resetting to the list.
    if (localStorage.getItem(VIEW_STORAGE_KEY) === 'graph') {
        showGraphView();
    }
});
</script>
@endsection
