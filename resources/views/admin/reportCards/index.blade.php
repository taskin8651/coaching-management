@extends('layouts.admin')

@section('page-title', 'Report Cards')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Report Cards</h2>
        <p class="admin-page-subtitle">
            Generated result summaries, parent visibility and publish status
        </p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Report Cards</p>
        <p class="stat-value">{{ $reportCards->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Published</p>
        <p class="stat-value">{{ $reportCards->where('published_to_parent', 1)->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Draft</p>
        <p class="stat-value">{{ $reportCards->where('published_to_parent', 0)->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Average %</p>
        <p class="stat-value">
            {{ $reportCards->count() ? number_format($reportCards->avg('percentage'), 1) : 0 }}%
        </p>
    </div>
</div>

@can('report_card_create')
    <div class="page-card" style="margin-bottom:16px;">
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
                    <th>Marks</th>
                    <th>Percentage</th>
                    <th>Grade</th>
                    <th>Rank</th>
                    <th>Parent Status</th>
                    <th style="text-align:right;">Publish</th>
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
                            <p class="table-sub-text">{{ $card->exam->exam_type ?? 'Exam' }}</p>
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

                        <td>
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