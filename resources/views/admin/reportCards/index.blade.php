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

@php
    $selectedExamTypes = collect($filters['exam_types'] ?? [])->map(fn ($value) => (string) $value)->all();
    $selectedSubjectIds = collect($filters['subject_ids'] ?? [])->map(fn ($value) => (string) $value)->all();
    $selectedStudentIds = collect($filters['student_ids'] ?? [])->map(fn ($value) => (string) $value)->all();
@endphp

<div class="page-card no-print" style="margin-bottom:16px;">

    <div class="page-card-header">

        <p class="page-card-title">
            Report Card Data Sheet Filters
        </p>

        <span class="page-card-note">
            <i class="fas fa-filter"></i>
            Batch -> Month/Date Range -> Test Type -> Subject -> Student
        </span>

    </div>


    <form method="GET"
          action="{{ route('admin.report-cards.index') }}"
          style="padding:16px;">

        {{-- ROW 1 : Batch + Month + Date Range --}}
        <div class="admin-form-grid"
             style="grid-template-columns: 1fr 1fr 2fr; gap:16px; margin-bottom:16px;">

            {{-- Batch --}}
            <div class="field-group mb-0">

                <label class="field-label" for="filter_batch_id">
                    1. Batch
                </label>

                <select name="batch_id"
                        id="filter_batch_id"
                        class="field-input js-rc-batch">

                    @foreach($batches as $id => $name)

                        <option value="{{ $id }}"
                            {{ ($filters['batch_id'] ?? '') == $id ? 'selected' : '' }}>

                            {{ $name }}

                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Month --}}
            <div class="field-group mb-0">

                <label class="field-label" for="filter_report_month">
                    2. Month
                </label>

                <input type="month"
                       name="report_month"
                       id="filter_report_month"
                       value="{{ $filters['report_month'] ?? '' }}"
                       class="field-input">

            </div>


            {{-- Date Range --}}
            <div class="field-group mb-0">

                <label class="field-label">
                    Date Range
                </label>

                <div style="display:flex; gap:8px;">

                    <input type="date"
                           name="date_from"
                           value="{{ $filters['date_from'] ?? '' }}"
                           class="field-input"
                           style="flex:1;">

                    <input type="date"
                           name="date_to"
                           value="{{ $filters['date_to'] ?? '' }}"
                           class="field-input"
                           style="flex:1;">

                </div>

            </div>

        </div>


        {{-- ROW 2 : Test Type + Subject --}}
        <div class="admin-form-grid"
             style="grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:16px;">

            {{-- Test Type --}}
            <div class="field-group mb-0 border p-4 rounded-md border-blue-900">

                <label class="field-label">
                    3. Test Type
                </label>

                <div class="form-mini-actions" style="margin-bottom:8px;">

                    <button type="button"
                            class="btn-mini-primary"
                            data-check-all="#filter_exam_types .role-checkbox-item">
                        All
                    </button>

                    <button type="button"
                            class="btn-mini-ghost"
                            data-uncheck-all="#filter_exam_types .role-checkbox-item">
                        None
                    </button>

                </div>


                <div class="checkbox-grid "
                     id="filter_exam_types">

                    @foreach($testTypes as $value => $label)

                        <label class="role-checkbox-item
                            {{ in_array((string) $value, $selectedExamTypes, true) ? 'checked' : '' }}">

                            <input type="checkbox"
                                   name="exam_types[]"
                                   value="{{ $value }}"
                                   class="role-checkbox"
                                   {{ in_array((string) $value, $selectedExamTypes, true) ? 'checked' : '' }}>

                            <div class="check-icon"></div>

                            <span class="checkbox-text">
                                {{ $label }}
                            </span>

                        </label>

                    @endforeach

                </div>

            </div>


            {{-- Subject --}}
            <div class="field-group mb-0 border p-4 rounded-md border-blue-900">

                <label class="field-label">
                    4. Subject
                </label>

                <div class="form-mini-actions" style="margin-bottom:8px;">

                    <button type="button"
                            class="btn-mini-primary"
                            data-check-all="#filter_subject_ids .role-checkbox-item">
                        All
                    </button>

                    <button type="button"
                            class="btn-mini-ghost"
                            data-uncheck-all="#filter_subject_ids .role-checkbox-item">
                        None
                    </button>

                </div>


                <div class="checkbox-grid js-rc-subjects"
                     id="filter_subject_ids"
                     data-selected-subjects='@json($selectedSubjectIds)'>

                </div>

            </div>

        </div>


        {{-- ROW 3 : Student --}}
        <div class="admin-form-grid border p-4 rounded-md border-blue-900"
             style="grid-template-columns: 1fr; gap:16px;">

            <div class="field-group mb-0">

                <label class="field-label">
                    5. Student
                </label>


                {{-- All Students --}}
                <label class="field-label"
                       style="
                           margin-bottom:10px;
                           display:flex;
                           align-items:center;
                           gap:8px;
                           cursor:pointer;
                       ">

                  

                </label>


                <div class="form-mini-actions"
                     style="margin-bottom:8px;">

                    <button type="button"
                            class="btn-mini-primary"
                            data-check-all="#filter_student_ids .role-checkbox-item">
                        All
                    </button>

                    <button type="button"
                            class="btn-mini-ghost"
                            data-uncheck-all="#filter_student_ids .role-checkbox-item">
                        None
                    </button>

                </div>


                <div class="checkbox-grid js-rc-students"
                     id="filter_student_ids"
                     data-selected-students='@json($selectedStudentIds)'>

                </div>

            </div>

        </div>


        {{-- ACTIONS --}}
        <div class="action-row"
             style="
                 justify-content:flex-start;
                 gap:10px;
                 margin-top:16px;
                 padding-top:14px;
                 border-top:1px solid #eee;
             ">

            <button type="submit"
                    class="btn-primary">

                <i class="fas fa-filter"></i>
                Apply Filters

            </button>


            <a href="{{ route('admin.report-cards.index') }}"
               class="btn-ghost">

                Clear

            </a>

        </div>

    </form>

</div>
@can('report_card_create')

    <div class="page-card no-print" style="margin-bottom:16px;">

        <div class="page-card-header">

            <p class="page-card-title">
                Create Report Card Data Sheet
            </p>

            <span class="page-card-note">
                <i class="fas fa-file-alt"></i>
                Same flow: Batch -> Month/Date Range -> Test Type -> Subject -> Student
            </span>

        </div>


        <div style="padding:16px;">

            <form method="POST"
                  action="{{ route('admin.report-cards.generate') }}">

                @csrf


                {{-- ROW 1 : BATCH + MONTH + DATE RANGE --}}
                <div class="admin-form-grid"
                     style="
                        grid-template-columns:1fr 1fr 2fr;
                        gap:16px;
                        margin-bottom:16px;
                     ">

                    {{-- 1. Batch --}}
                    <div class="field-group mb-0">

                        <label class="field-label"
                               for="generate_batch_id">

                            1. Batch
                            <span class="req">*</span>

                        </label>

                        <select name="batch_id"
                                id="generate_batch_id"
                                required
                                class="field-input js-rc-batch {{ $errors->has('batch_id') ? 'error' : '' }}">

                            @foreach($batches as $id => $name)

                                <option value="{{ $id }}"
                                    {{ old('batch_id', $filters['batch_id'] ?? '') == $id ? 'selected' : '' }}>

                                    {{ $name }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- 2. Month --}}
                    <div class="field-group mb-0">

                        <label class="field-label"
                               for="generate_report_month">

                            2. Month

                        </label>

                        <input type="month"
                               name="report_month"
                               id="generate_report_month"
                               value="{{ old('report_month', $filters['report_month'] ?? '') }}"
                               class="field-input">

                    </div>


                    {{-- Date Range --}}
                    <div class="field-group mb-0">

                        <label class="field-label">
                            Date Range
                        </label>

                        <div style="display:flex; gap:8px;">

                            <input type="date"
                                   name="date_from"
                                   value="{{ old('date_from', $filters['date_from'] ?? '') }}"
                                   class="field-input"
                                   style="flex:1;">

                            <input type="date"
                                   name="date_to"
                                   value="{{ old('date_to', $filters['date_to'] ?? '') }}"
                                   class="field-input"
                                   style="flex:1;">

                        </div>

                    </div>

                </div>


                {{-- TEST TYPE + SUBJECT + STUDENT BORDER --}}
                <div style="
                    border:1px solid #e5e7eb;
                    border-radius:10px;
                    padding:16px;
                    margin-bottom:16px;
                    background:#fff;
                ">

                    {{-- Section Header --}}
                    <div style="
                        display:flex;
                        align-items:center;
                        gap:8px;
                        margin-bottom:16px;
                        padding-bottom:12px;
                        border-bottom:1px solid #eee;
                    ">

                        <i class="fas fa-filter"
                           style="font-size:13px;">
                        </i>

                        <strong style="font-size:13px;">
                            Test Type, Subject & Student Selection
                        </strong>

                    </div>


                    {{-- ROW 2 : TEST TYPE + SUBJECT --}}
                    <div class="admin-form-grid"
                         style="
                            grid-template-columns:1fr 1fr;
                            gap:16px;
                            margin-bottom:16px;
                         ">


                        {{-- 3. Test Type --}}
                        <div class="field-group mb-0 border p-4 rounded-md border-blue-900">

                            <label class="field-label">
                                3. Test Type
                            </label>


                            <div class="form-mini-actions"
                                 style="
                                    margin-bottom:8px;
                                    display:flex;
                                    justify-content:flex-end;
                                    gap:6px;
                                 ">

                                <button type="button"
                                        class="btn-mini-primary"
                                        data-check-all="#generate_exam_types .role-checkbox-item">

                                    All

                                </button>

                                <button type="button"
                                        class="btn-mini-ghost"
                                        data-uncheck-all="#generate_exam_types .role-checkbox-item">

                                    None

                                </button>

                            </div>


                            <div class="checkbox-grid"
                                 id="generate_exam_types">

                                @php
                                    $selectedExamTypes = collect(
                                        old('exam_types', $filters['exam_types'] ?? [])
                                    )
                                    ->map(fn ($v) => (string) $v)
                                    ->all();
                                @endphp


                                @foreach($testTypes as $value => $label)

                                    <label class="role-checkbox-item
                                        {{ in_array((string) $value, $selectedExamTypes, true) ? 'checked' : '' }}">

                                        <input type="checkbox"
                                               name="exam_types[]"
                                               value="{{ $value }}"
                                               class="role-checkbox"
                                               {{ in_array((string) $value, $selectedExamTypes, true) ? 'checked' : '' }}>

                                        <div class="check-icon"></div>

                                        <span class="checkbox-text">
                                            {{ $label }}
                                        </span>

                                    </label>

                                @endforeach

                            </div>

                        </div>


                        {{-- 4. Subject --}}
                        <div class="field-group mb-0 border p-4 rounded-md border-blue-900">

                            <label class="field-label">
                                4. Subject
                            </label>


                            <div class="form-mini-actions"
                                 style="
                                    margin-bottom:8px;
                                    display:flex;
                                    justify-content:flex-end;
                                    gap:6px;
                                 ">

                                <button type="button"
                                        class="btn-mini-primary"
                                        data-check-all="#generate_subject_ids .role-checkbox-item">

                                    All

                                </button>

                                <button type="button"
                                        class="btn-mini-ghost"
                                        data-uncheck-all="#generate_subject_ids .role-checkbox-item">

                                    None

                                </button>

                            </div>


                            <div class="checkbox-grid js-rc-subjects"
                                 id="generate_subject_ids"
                                 data-selected-subjects='@json(
                                    collect(old('subject_ids', $filters['subject_ids'] ?? []))
                                    ->map(fn($v) => (string)$v)
                                    ->all()
                                 )'>

                            </div>

                        </div>

                    </div>


                    {{-- 5. STUDENT --}}
                    <div class="admin-form-grid"
                         style="
                            grid-template-columns:1fr;
                            gap:16px;
                         ">

                        <div class="field-group mb-0 border p-4 rounded-md border-blue-900">

                            <label class="field-label">
                                5. Student
                            </label>


                            {{-- All Students --}}
                            <label class="field-label"
                                   style="
                                        margin-bottom:10px;
                                        display:flex;
                                        align-items:center;
                                        gap:8px;
                                        cursor:pointer;
                                   ">

                                <input type="checkbox"
                                       name="all_students"
                                       value="1"
                                       class="js-rc-all-students"
                                       {{ old('all_students', $filters['all_students'] ?? true) ? 'checked' : '' }}>

                                <span>
                                    All Select
                                </span>

                            </label>


                            <div class="form-mini-actions"
                                 style="
                                    margin-bottom:8px;
                                    display:flex;
                                    justify-content:flex-end;
                                    gap:6px;
                                 ">

                                <button type="button"
                                        class="btn-mini-primary"
                                        data-check-all="#generate_student_ids .role-checkbox-item">

                                    All

                                </button>

                                <button type="button"
                                        class="btn-mini-ghost"
                                        data-uncheck-all="#generate_student_ids .role-checkbox-item">

                                    None

                                </button>

                            </div>


                            {{-- Students --}}
                            <div class="checkbox-grid js-rc-students"
                                 id="generate_student_ids"
                                 data-selected-students='@json(
                                    collect(old('student_ids', $filters['student_ids'] ?? []))
                                    ->map(fn($v) => (string)$v)
                                    ->all()
                                 )'>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- ACTION --}}
                <div class="action-row"
                     style="
                        justify-content:flex-start;
                        gap:12px;
                        margin-top:14px;
                        padding-top:14px;
                        border-top:1px solid #eee;
                     ">

                    <button type="submit"
                            class="btn-primary">

                        <i class="fas fa-sync-alt"></i>

                        Create / Update Data Sheet

                    </button>

                </div>


                {{-- ERROR --}}
                @if($errors->any())

                    <p class="field-error"
                       style="margin-top:8px;">

                        <i class="fas fa-exclamation-circle"></i>

                        {{ $errors->first() }}

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
                    <th>Batch</th>
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
    const reportCardOptions = @json($filterOptions);
    const selectedSubjectIds = @json($selectedSubjectIds);
    const selectedStudentIds = @json($selectedStudentIds);

    function checkboxHtml(items, selectedIds, fieldName) {
        return (items || []).map(function (item) {
            const id = String(item.id);
            const checked = selectedIds.includes(id) ? 'checked' : '';
            return `
                <label class="role-checkbox-item ${checked ? 'checked' : ''}">
                    <input type="checkbox" name="${fieldName}" value="${item.id}" class="role-checkbox" ${checked}>
                    <div class="check-icon"></div>
                    <span class="checkbox-text">${item.name}</span>
                </label>
            `;
        }).join('');
    }

    function bindCheckboxCardBehavior(root) {
        root.querySelectorAll('.role-checkbox-item').forEach(function (item) {
            const checkbox = item.querySelector('input[type=checkbox]');
            if (!checkbox) return;

            item.classList.toggle('checked', checkbox.checked);

            item.onclick = function (event) {
                event.preventDefault();
                checkbox.checked = !checkbox.checked;
                item.classList.toggle('checked', checkbox.checked);
            };
        });
    }

    function syncReportCardSelector(root) {
        const batchSelect = root.querySelector('.js-rc-batch');
        const subjectGrid = root.querySelector('.js-rc-subjects');
        const studentGrid = root.querySelector('.js-rc-students');
        const allStudents = root.querySelector('.js-rc-all-students');

        if (!batchSelect || !subjectGrid || !studentGrid) {
            return;
        }

        const batchId = batchSelect.value || '';
        const selectedSubjects = JSON.parse(subjectGrid.dataset.selectedSubjects || '[]');
        const selectedStudents = JSON.parse(studentGrid.dataset.selectedStudents || '[]');

        subjectGrid.innerHTML = checkboxHtml(reportCardOptions.subjectsByBatch[batchId] || [], selectedSubjects, 'subject_ids[]');
        studentGrid.innerHTML = checkboxHtml(reportCardOptions.studentsByBatch[batchId] || [], selectedStudents, 'student_ids[]');
        bindCheckboxCardBehavior(root);

        if (allStudents) {
            const disableStudentGrid = allStudents.checked;
            studentGrid.style.opacity = disableStudentGrid ? '0.7' : '1';
            studentGrid.style.pointerEvents = disableStudentGrid ? 'none' : 'auto';
            allStudents.onchange = function () {
                const studentGridNow = root.querySelector('.js-rc-students');
                const disableNow = this.checked;
                studentGridNow.style.opacity = disableNow ? '0.7' : '1';
                studentGridNow.style.pointerEvents = disableNow ? 'none' : 'auto';
            };
        }
    }

    document.querySelectorAll('form').forEach(function (form) {
        if (!form.querySelector('.js-rc-batch')) {
            return;
        }

        syncReportCardSelector(form);

        form.querySelector('.js-rc-batch').addEventListener('change', function () {
            syncReportCardSelector(form);
        });
    });

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
