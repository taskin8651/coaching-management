
@extends('layouts.admin')

@section('page-title', 'Report Card Preview')

@section('styles')
<style>
.report-preview-page {
    --rp-primary: #1e3a5f;
    --rp-accent: #2563eb;
    --rp-text: #172033;
    --rp-muted: #64748b;
    --rp-border: #dbe3ee;
    color: var(--rp-text);
}

/* Screen summary cards */
.report-preview-page .report-summary-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
    margin: 20px 0;
}

.report-preview-page .report-summary-card {
    min-width: 0;
    padding: 17px;
    background: #fff;
    border: 1px solid var(--rp-border);
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(15,23,42,.04);
}

.report-preview-page .report-summary-label {
    color: var(--rp-muted);
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    margin: 0 0 8px;
}

.report-preview-page .report-summary-value {
    color: var(--rp-primary);
    font-size: 25px;
    font-weight: 800;
    line-height: 1.2;
    margin: 0;
    overflow-wrap: anywhere;
}

.report-preview-page .report-summary-note {
    color: var(--rp-muted);
    font-size: 11px;
    margin: 6px 0 0;
}

.report-preview-page .report-actions {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

/* Reuse timetable UI classes */
.report-preview-page .timetable-card {
    background: #fff;
    border: 1px solid var(--rp-border);
    border-radius: 14px;
    overflow: hidden;
    margin: 20px auto;
    box-shadow: 0 5px 24px rgba(15,23,42,.045);
}

.report-preview-page .timetable-card-body {
    padding: 0;
}

/* Report header */
.report-preview-page .report-header {
    position: relative;
    overflow: hidden;
    padding: 26px 28px;
    color: #fff;
    background: linear-gradient(120deg, #172f50, #1e3a5f 62%, #28558b);
}

.report-preview-page .report-header-top {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 15px;
}

.report-preview-page .report-eyebrow {
    color: #bfdbfe;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin: 0 0 8px;
}

.report-preview-page .report-title {
    color: #fff;
    font-size: 27px;
    line-height: 1.2;
    font-weight: 800;
    margin: 0;
}

.report-preview-page .report-subtitle {
    color: #dbeafe;
    font-size: 12px;
    margin: 8px 0 0;
}

.report-preview-page .report-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 11px;
    border: 1px solid rgba(255,255,255,.28);
    border-radius: 8px;
    background: rgba(255,255,255,.08);
    font-size: 10px;
    font-weight: 700;
    white-space: nowrap;
}

/* Student details */
.report-preview-page .report-student-info {
    display: grid;
    grid-template-columns: 1.4fr 1fr 1fr;
    gap: 14px;
    padding: 18px 28px;
    background: #f7faff;
    border-bottom: 1px solid var(--rp-border);
}

.report-preview-page .student-field {
    min-width: 0;
    padding-left: 11px;
    border-left: 3px solid #bfdbfe;
}

.report-preview-page .student-field label {
    display: block;
    color: var(--rp-muted);
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .05em;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.report-preview-page .student-field strong {
    display: block;
    color: var(--rp-text);
    font-size: 12px;
    overflow-wrap: anywhere;
}

/* Main content */
.report-preview-page .report-content {
    padding: 23px 28px 25px;
}

.report-preview-page .report-section-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin: 0 0 12px;
}

.report-preview-page .report-section-title h3 {
    color: var(--rp-primary);
    font-size: 13px;
    font-weight: 800;
    margin: 0;
}

.report-preview-page .report-section-title span {
    color: var(--rp-muted);
    font-size: 10px;
}

/* Performance cards */
.report-preview-page .report-kpi-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 23px;
}

.report-preview-page .report-kpi {
    min-width: 0;
    padding: 13px 15px;
    border: 1px solid var(--rp-border);
    border-radius: 10px;
}

.report-preview-page .report-kpi-label {
    display: block;
    color: var(--rp-muted);
    font-size: 9px;
    font-weight: 800;
    text-transform: uppercase;
    margin-bottom: 7px;
}

.report-preview-page .report-kpi-value {
    display: block;
    color: var(--rp-primary);
    font-size: 21px;
    line-height: 1.2;
    font-weight: 800;
    overflow-wrap: anywhere;
}

.report-preview-page .report-kpi-note {
    display: block;
    color: var(--rp-muted);
    font-size: 10px;
    margin-top: 5px;
}

/* Results table */
.report-preview-page .timetable-table-wrap {
    width: 100%;
    overflow-x: auto;
    border: 1px solid var(--rp-border);
    border-radius: 9px;
}

.report-preview-page .report-table {
    width: 100%;
    min-width: 900px;
    margin: 0;
    border-collapse: collapse !important;
    border-spacing: 0 !important;
    background: #fff;
    table-layout: auto;
}

.report-preview-page .report-table th,
.report-preview-page .report-table td {
    padding: 10px 11px;
    border-bottom: 1px solid var(--rp-border);
    text-align: left;
    vertical-align: top;
    font-size: 11px;
    line-height: 1.45;
}

.report-preview-page .report-table th {
    background: #f1f5f9;
    color: #334155;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .025em;
    text-transform: uppercase;
    white-space: nowrap;
}

.report-preview-page .report-table tbody tr:nth-child(even) {
    background: #fbfdff;
}

.report-preview-page .report-table tbody tr:last-child td {
    border-bottom: 0;
}

.report-preview-page .test-name {
    min-width: 105px;
    color: var(--rp-text);
    font-weight: 750;
}

.report-preview-page .report-table small {
    display: block;
    color: var(--rp-muted);
    font-size: 9px;
    margin-top: 3px;
}

.report-preview-page .marks-value {
    font-weight: 750;
    white-space: nowrap;
    font-variant-numeric: tabular-nums;
}

.report-preview-page .result-pill {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 30px;
    background: #f1f5f9;
    color: #475569;
    font-size: 9px;
    font-weight: 800;
    white-space: nowrap;
}

.report-preview-page .result-pill.pass {
    background: #dcfce7;
    color: #166534;
}

.report-preview-page .result-pill.fail {
    background: #fee2e2;
    color: #991b1b;
}

.report-preview-page .remark-cell {
    min-width: 125px;
    max-width: 220px;
    white-space: pre-line;
    overflow-wrap: anywhere;
}

.report-preview-page .no-remark {
    color: #94a3b8;
    font-style: italic;
}

.report-preview-page .teacher-cell {
    min-width: 100px;
    font-weight: 650;
}

/* Combined performance panel */
.report-preview-page .combined-panel {
    margin-top: 18px;
    border: 1px solid #bfdbfe;
    border-radius: 10px;
    overflow: hidden;
}

.report-preview-page .combined-panel-heading {
    padding: 11px 14px;
    background: #eff6ff;
    color: #1e40af;
    font-size: 11px;
    font-weight: 800;
}

.report-preview-page .combined-panel-body {
    padding: 13px 14px;
    color: #334155;
    font-size: 11px;
    line-height: 1.6;
    white-space: pre-line;
    overflow-wrap: anywhere;
}

.report-preview-page .report-signatures {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    margin-top: 34px;
}

.report-preview-page .signature-line {
    padding-top: 7px;
    border-top: 1px solid #94a3b8;
    color: var(--rp-muted);
    font-size: 9px;
    text-align: center;
}

.report-preview-page .report-footer {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 11px 28px;
    background: #fbfdff;
    border-top: 1px solid var(--rp-border);
    color: var(--rp-muted);
    font-size: 9px;
}

.report-preview-page .report-empty {
    padding: 45px 20px;
    text-align: center;
    background: #fff;
    border: 1px dashed var(--rp-border);
    border-radius: 12px;
}

.report-preview-page .report-empty h3 {
    color: var(--rp-primary);
    margin: 8px 0;
}

.report-preview-page .report-empty p {
    color: var(--rp-muted);
    margin: 0;
}

/* Responsive screen UI */
@media (max-width: 900px) {
    .report-preview-page .report-summary-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .report-preview-page .report-student-info {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 560px) {
    .report-preview-page .report-header,
    .report-preview-page .report-content {
        padding: 18px 14px;
    }

    .report-preview-page .report-header-top {
        flex-direction: column;
    }

    .report-preview-page .report-title {
        font-size: 23px;
    }

    .report-preview-page .report-student-info {
        padding: 14px;
        gap: 12px;
    }

    .report-preview-page .report-summary-card {
        padding: 12px;
    }

    .report-preview-page .report-summary-value {
        font-size: 21px;
    }

    .report-preview-page .report-kpi-grid {
        gap: 8px;
    }

    .report-preview-page .report-kpi {
        padding: 10px;
    }

    .report-preview-page .report-kpi-value {
        font-size: 17px;
    }

    .report-preview-page .report-footer {
        flex-direction: column;
        padding: 12px 14px;
    }
}

/* ==========================================
   PRINT CSS — A4 LANDSCAPE
   Keep report in normal flow. Do not hide
   all body elements or absolutely position it.
========================================== */
@page {
    size: A4 landscape;
    margin: 8mm;
}

@media print {
    html,
    body {
        width: 100% !important;
        height: auto !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: visible !important;
        background: #fff !important;
        color: #111827 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Hide admin layout only */
    .admin-sidebar,
    .sidebar,
    .admin-topbar,
    .admin-header,
    .navbar,
    .sidebar-overlay,
    .no-print,
    .admin-page-head {
        display: none !important;
    }

    .report-preview-page {
        display: block !important;
        position: static !important;
        inset: auto !important;
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: visible !important;
        color: #111827 !important;
    }

    .report-preview-page .report-summary-grid {
        display: none !important;
    }

    .report-preview-page .timetable-card {
        display: block !important;
        width: 100% !important;
        max-width: none !important;
        margin: 0 0 8mm !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 0 !important;
        overflow: visible !important;
        background: #fff !important;
        box-shadow: none !important;
        break-after: page;
        page-break-after: always;
    }

    .report-preview-page .timetable-card:last-child {
        break-after: auto !important;
        page-break-after: auto !important;
    }

    .report-preview-page .timetable-card-body,
    .report-preview-page .report-content {
        display: block !important;
        width: 100% !important;
        overflow: visible !important;
    }

    .report-preview-page .report-header {
        padding: 12px 16px !important;
        background: #1e3a5f !important;
        color: #fff !important;
        break-inside: avoid;
        page-break-inside: avoid;
    }

    .report-preview-page .report-header:after {
        display: none !important;
    }

    .report-preview-page .report-header-top {
        flex-direction: row !important;
        align-items: flex-start !important;
    }

    .report-preview-page .report-title {
        color: #fff !important;
        font-size: 21px !important;
    }

    .report-preview-page .report-eyebrow {
        font-size: 8px !important;
        margin-bottom: 5px !important;
    }

    .report-preview-page .report-subtitle {
        color: #e5eefb !important;
        font-size: 9px !important;
        margin-top: 4px !important;
    }

    .report-preview-page .report-badge {
        padding: 6px 8px !important;
        font-size: 8px !important;
    }

    .report-preview-page .report-student-info {
        display: grid !important;
        grid-template-columns: 1.4fr 1fr 1fr !important;
        gap: 8px !important;
        padding: 9px 16px !important;
        background: #f7faff !important;
        break-inside: avoid;
        page-break-inside: avoid;
    }

    .report-preview-page .student-field label {
        font-size: 7px !important;
        margin-bottom: 3px !important;
    }

    .report-preview-page .student-field strong {
        font-size: 9px !important;
    }

    .report-preview-page .report-content {
        padding: 12px 16px !important;
    }

    .report-preview-page .report-section-title {
        margin: 0 0 7px !important;
        break-after: avoid;
        page-break-after: avoid;
    }

    .report-preview-page .report-section-title h3 {
        font-size: 10px !important;
    }

    .report-preview-page .report-section-title span {
        font-size: 8px !important;
    }

    .report-preview-page .report-kpi-grid {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 7px !important;
        margin-bottom: 13px !important;
        break-inside: avoid;
        page-break-inside: avoid;
    }

    .report-preview-page .report-kpi {
        padding: 8px 10px !important;
        border-radius: 4px !important;
        break-inside: avoid;
        page-break-inside: avoid;
    }

    .report-preview-page .report-kpi-label {
        font-size: 7px !important;
        margin-bottom: 4px !important;
    }

    .report-preview-page .report-kpi-value {
        font-size: 15px !important;
    }

    .report-preview-page .report-kpi-note {
        font-size: 7px !important;
        margin-top: 3px !important;
    }

    /* Important: no horizontal scrolling container in print */
    .report-preview-page .timetable-table-wrap {
        display: block !important;
        width: 100% !important;
        max-width: none !important;
        overflow: visible !important;
        border: 0 !important;
        border-radius: 0 !important;
    }

    .report-preview-page .report-table {
        display: table !important;
        width: 100% !important;
        min-width: 0 !important;
        max-width: none !important;
        table-layout: fixed !important;
        border-collapse: collapse !important;
        page-break-inside: auto;
    }

    .report-preview-page .report-table thead {
        display: table-header-group !important;
    }

    .report-preview-page .report-table tfoot {
        display: table-footer-group !important;
    }

    .report-preview-page .report-table tr {
        break-inside: avoid !important;
        page-break-inside: avoid !important;
    }

    .report-preview-page .report-table th,
    .report-preview-page .report-table td {
        padding: 5px !important;
        border: 1px solid #cbd5e1 !important;
        color: #1f2937 !important;
        font-size: 7.2px !important;
        line-height: 1.25 !important;
        vertical-align: top !important;
        white-space: normal !important;
        overflow-wrap: anywhere !important;
        word-break: normal !important;
    }

    .report-preview-page .report-table th {
        background: #eaf0f7 !important;
        color: #172033 !important;
        font-size: 6.8px !important;
        font-weight: 800 !important;
    }

    /* Eight columns fit within the landscape page */
    .report-preview-page .report-table th:nth-child(1),
    .report-preview-page .report-table td:nth-child(1) { width: 13% !important; }

    .report-preview-page .report-table th:nth-child(2),
    .report-preview-page .report-table td:nth-child(2) { width: 9% !important; }

    .report-preview-page .report-table th:nth-child(3),
    .report-preview-page .report-table td:nth-child(3) { width: 10% !important; }

    .report-preview-page .report-table th:nth-child(4),
    .report-preview-page .report-table td:nth-child(4) { width: 8% !important; }

    .report-preview-page .report-table th:nth-child(5),
    .report-preview-page .report-table td:nth-child(5) { width: 10% !important; }

    .report-preview-page .report-table th:nth-child(6),
    .report-preview-page .report-table td:nth-child(6) { width: 16% !important; }

    .report-preview-page .report-table th:nth-child(7),
    .report-preview-page .report-table td:nth-child(7) { width: 16% !important; }

    .report-preview-page .report-table th:nth-child(8),
    .report-preview-page .report-table td:nth-child(8) { width: 18% !important; }

    .report-preview-page .test-name {
        min-width: 0 !important;
        font-size: 7.2px !important;
    }

    .report-preview-page .report-table small {
        font-size: 6.5px !important;
        margin-top: 2px !important;
    }

    .report-preview-page .marks-value {
        white-space: normal !important;
    }

    .report-preview-page .result-pill {
        padding: 2px 4px !important;
        font-size: 6.5px !important;
        white-space: normal !important;
        border: 1px solid #d1d5db !important;
    }

    .report-preview-page .remark-cell,
    .report-preview-page .teacher-cell {
        min-width: 0 !important;
        max-width: none !important;
        white-space: pre-line !important;
        overflow-wrap: anywhere !important;
    }

    .report-preview-page .combined-panel {
        margin-top: 10px !important;
        border-radius: 4px !important;
        break-inside: avoid;
        page-break-inside: avoid;
    }

    .report-preview-page .combined-panel-heading {
        padding: 7px 9px !important;
        font-size: 8px !important;
        background: #eaf2ff !important;
    }

    .report-preview-page .combined-panel-body {
        padding: 8px 9px !important;
        font-size: 8px !important;
        line-height: 1.4 !important;
    }

    .report-preview-page .report-signatures {
        margin-top: 22px !important;
        gap: 55px !important;
        break-inside: avoid;
        page-break-inside: avoid;
    }

    .report-preview-page .signature-line {
        font-size: 7px !important;
    }

    .report-preview-page .report-footer {
        padding: 7px 16px !important;
        background: #fff !important;
        font-size: 7px !important;
    }

    .report-preview-page .report-empty {
        display: none !important;
    }
}
</style>
@endsection

@section('content')
<div class="report-preview-page">

    <div class="admin-page-head no-print">
        <div>
            <a href="{{ route('admin.report-cards.index') }}" class="admin-back-link">
                <i class="fas fa-arrow-left"></i> Report Cards
            </a>

            <h2 class="admin-page-title">Live Report Card Preview</h2>

            <p class="admin-page-subtitle">
                Combined student performance with the latest saved results and teacher remarks.
            </p>
        </div>

        <div class="report-actions">
            <button type="button" class="btn-ghost" onclick="window.location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>

            <button type="button" class="btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Download / Print PDF
            </button>
        </div>
    </div>

    @php
        $groupedStudentResults = $results->groupBy('student_id');
        $studentCount = $groupedStudentResults->count();
        $totalResults = $results->count();
        $allMarks = $results->sum('marks_obtained');
        $allTotalMarks = $results->sum('total_marks');

        $overallPercentage = $allTotalMarks > 0
            ? ($allMarks / $allTotalMarks) * 100
            : null;
    @endphp

    <div class="report-summary-grid no-print">
        <div class="report-summary-card">
            <p class="report-summary-label">
                <i class="fas fa-user-graduate"></i> Students
            </p>
            <p class="report-summary-value">{{ $studentCount }}</p>
            <p class="report-summary-note">Students in this preview</p>
        </div>

        <div class="report-summary-card">
            <p class="report-summary-label">
                <i class="fas fa-file-alt"></i> Test Results
            </p>
            <p class="report-summary-value">{{ $totalResults }}</p>
            <p class="report-summary-note">Saved result records</p>
        </div>

        <div class="report-summary-card">
            <p class="report-summary-label">
                <i class="fas fa-chart-line"></i> Combined Score
            </p>
            <p class="report-summary-value">
                {{ $overallPercentage !== null ? number_format($overallPercentage, 2).'%' : '—' }}
            </p>
            <p class="report-summary-note">Weighted by total marks</p>
        </div>

        <div class="report-summary-card">
            <p class="report-summary-label">
                <i class="fas fa-clock"></i> Preview Date
            </p>
            <p class="report-summary-value" style="font-size:19px;">
                {{ now()->format('d M Y') }}
            </p>
            <p class="report-summary-note">{{ now()->format('h:i A') }}</p>
        </div>
    </div>

    @if($groupedStudentResults->isEmpty())

        <div class="report-empty">
            <i class="fas fa-clipboard-list" style="font-size:30px;color:#2563eb;"></i>
            <h3>No Report Results Found</h3>
            <p>No saved test results are available for this preview.</p>
        </div>

    @else

        @foreach($groupedStudentResults as $studentResults)

            @php
                $firstResult = $studentResults->first();
                $reportStudent = $firstResult->student;

                $combinedMarks = $studentResults->sum('marks_obtained');
                $combinedTotal = $studentResults->sum('total_marks');

                $combinedPercentage = $combinedTotal > 0
                    ? ($combinedMarks / $combinedTotal) * 100
                    : null;

                $numberOfTests = $studentResults->count();

                $subjectCount = $studentResults->map(function ($result) {
                    return $result->exam->subject->name ?? null;
                })->filter()->unique()->count();

                $remarkCount = $studentResults->filter(function ($result) {
                    return filled($result->remarks)
                        || filled($result->overall_remark);
                })->count();
            @endphp

            <div class="timetable-card report-sheet">
                <div class="timetable-card-body">

                    <div class="report-header">
                        <div class="report-header-top">
                            <div>
                                <div class="report-eyebrow">
                                    Academic Performance · Student Report
                                </div>

                                <h1 class="report-title">Report Card</h1>

                                <p class="report-subtitle">
                                    Consolidated test results, subject feedback and teacher remarks
                                </p>
                            </div>

                            <div class="report-badge">
                                <i class="fas fa-award"></i>
                                Performance Summary
                            </div>
                        </div>
                    </div>

                    <div class="report-student-info">
                        <div class="student-field">
                            <label>Student Name</label>
                            <strong>{{ $reportStudent->user->name ?? 'Student' }}</strong>
                        </div>

                        <div class="student-field">
                            <label>Student ID</label>
                            <strong>{{ $reportStudent->student_code ?? '—' }}</strong>
                        </div>

                        <div class="student-field">
                            <label>Batch / Class</label>
                            <strong>
                                {{ $reportStudent->batch->name ?? ($firstResult->exam->batch->name ?? '—') }}
                            </strong>
                        </div>
                    </div>

                    <div class="report-content">

                        <div class="report-section-title">
                            <h3>
                                <i class="fas fa-chart-bar"></i> Performance Overview
                            </h3>

                            <span>
                                {{ $numberOfTests }} test(s) · {{ $subjectCount }} subject(s)
                            </span>
                        </div>

                        <div class="report-kpi-grid">
                            <div class="report-kpi">
                                <span class="report-kpi-label">Combined Marks</span>

                                <span class="report-kpi-value">
                                    {{ number_format((float) $combinedMarks, 2) }}
                                    <span style="font-size:11px;color:#64748b;">
                                        / {{ number_format((float) $combinedTotal, 2) }}
                                    </span>
                                </span>

                                <span class="report-kpi-note">
                                    Obtained / maximum marks
                                </span>
                            </div>

                            <div class="report-kpi">
                                <span class="report-kpi-label">Overall Percentage</span>

                                <span class="report-kpi-value">
                                    {{ $combinedPercentage !== null ? number_format($combinedPercentage, 2).'%' : '—' }}
                                </span>

                                <span class="report-kpi-note">
                                    Across included tests
                                </span>
                            </div>

                            <div class="report-kpi">
                                <span class="report-kpi-label">Teacher Feedback</span>

                                <span class="report-kpi-value">
                                    {{ $remarkCount }}
                                    <span style="font-size:11px;color:#64748b;">
                                        / {{ $numberOfTests }}
                                    </span>
                                </span>

                                <span class="report-kpi-note">
                                    Tests with saved remarks
                                </span>
                            </div>
                        </div>

                        <div class="report-section-title">
                            <h3>
                                <i class="fas fa-list-alt"></i> Test-wise Results & Remarks
                            </h3>

                            <span>Latest saved information</span>
                        </div>

                        <div class="timetable-table-wrap">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>Test / Date</th>
                                        <th>Subject</th>
                                        <th>Marks</th>
                                        <th>Percentage</th>
                                        <th>Result / Rank</th>
                                        <th>Subject Remark</th>
                                        <th>Overall Remark</th>
                                        <th>Teacher / Date</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($studentResults as $result)

                                        @php
                                            $resultStatus = strtolower(
                                                (string) ($result->result_status ?? '')
                                            );

                                            $statusClass = in_array(
                                                $resultStatus,
                                                ['pass', 'passed'],
                                                true
                                            )
                                                ? 'pass'
                                                : (
                                                    in_array(
                                                        $resultStatus,
                                                        ['fail', 'failed'],
                                                        true
                                                    ) ? 'fail' : ''
                                                );
                                        @endphp

                                        <tr>
                                            <td>
                                                <div class="test-name">
                                                    {{ $result->exam->title ?? 'Untitled Test' }}
                                                </div>

                                                <small>
                                                    {{ optional($result->exam->exam_date)->format('d M Y') ?? 'Date not set' }}
                                                </small>
                                            </td>

                                            <td>
                                                {{ $result->exam->subject->name ?? '—' }}
                                            </td>

                                            <td class="marks-value">
                                                {{ number_format((float) ($result->marks_obtained ?? 0), 2) }}
                                                /
                                                {{ number_format((float) ($result->total_marks ?? 0), 2) }}
                                            </td>

                                            <td class="marks-value">
                                                {{ number_format((float) ($result->percentage ?? 0), 2) }}%
                                            </td>

                                            <td>
                                                <span class="result-pill {{ $statusClass }}">
                                                    {{ $result->result_status ? ucfirst($result->result_status) : 'Pending' }}
                                                </span>

                                                @if($result->rank)
                                                    <small>Rank #{{ $result->rank }}</small>
                                                @endif
                                            </td>

                                            <td class="remark-cell">
                                                @if(filled($result->remarks))
                                                    {{ $result->remarks }}
                                                @else
                                                    <span class="no-remark">No subject remark</span>
                                                @endif
                                            </td>

                                            <td class="remark-cell">
                                                @if(filled($result->overall_remark))
                                                    {{ $result->overall_remark }}
                                                @else
                                                    <span class="no-remark">No overall remark</span>
                                                @endif
                                            </td>

                                            <td class="teacher-cell">
                                                {{ $result->remarkAuthor->name ?? '—' }}

                                                <small>
                                                    {{ optional($result->remark_updated_at)->format('d M Y') ?? 'Date not recorded' }}
                                                </small>
                                            </td>
                                        </tr>

                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="combined-panel">
                            <div class="combined-panel-heading">
                                <i class="fas fa-comment-dots"></i>
                                &nbsp; Combined Performance Summary
                            </div>

                            <div class="combined-panel-body">
                                Student has {{ $numberOfTests }} included test(s)
                                across {{ $subjectCount }} subject(s).

                                Combined score:
                                {{ number_format((float) $combinedMarks, 2) }}
                                out of {{ number_format((float) $combinedTotal, 2) }} marks
                                @if($combinedPercentage !== null)
                                    ({{ number_format($combinedPercentage, 2) }}%)
                                @endif.

                                @if($remarkCount === 0)
                                    No teacher remarks have been saved for these tests yet.
                                @else
                                    Teacher feedback is available for {{ $remarkCount }} test(s)
                                    in the table above.
                                @endif
                            </div>
                        </div>

                        <div class="report-signatures">
                            <div class="signature-line">
                                Subject Teacher / Class Teacher
                            </div>

                            <div class="signature-line">
                                Authorized Signature
                            </div>
                        </div>

                    </div>

                    <div class="report-footer">
                        <span>
                            <strong>Report Card</strong> · Generated from saved examination records
                        </span>

                        <span>
                            Preview generated {{ now()->format('d M Y, h:i A') }}
                        </span>
                    </div>

                </div>
            </div>

        @endforeach

    @endif
</div>
@endsection
