@extends('layouts.admin')

@section('page-title', 'Show Exam')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.exams.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">{{ $exam->title }}</h2>

        <p class="admin-page-subtitle">
            Exam details, result entry and student marks report
        </p>
    </div>

    <div class="show-actions">
        @can('exam_edit')
            <a href="{{ route('admin.exams.edit', $exam->id) }}" class="btn-primary">
                <i class="fas fa-pencil-alt"></i>
                Edit Exam
            </a>
        @endcan
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Students</p>
        <p class="stat-value">{{ $students->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Results Entered</p>
        <p class="stat-value">{{ $exam->results->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Passed</p>
        <p class="stat-value">{{ $exam->results->where('result_status', 'pass')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Failed / Absent</p>
        <p class="stat-value">
            {{ $exam->results->whereIn('result_status', ['fail', 'absent'])->count() }}
        </p>
    </div>
</div>

<div class="detail-card mb-3">
    <div class="detail-section-head">
        <div class="detail-section-icon">
            <i class="fas fa-info-circle"></i>
        </div>

        <p class="detail-section-title">Exam Information</p>
    </div>

    <div class="detail-section-body">
        <div class="detail-row">
            <span class="detail-label">Branch</span>
            <span class="detail-value">{{ $exam->branch->name ?? '-' }}</span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Course</span>
            <span class="detail-value">{{ $exam->course->name ?? '-' }}</span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Batch</span>
            <span class="detail-value">{{ $exam->batch->name ?? '-' }}</span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Subject</span>
            <span class="detail-value">{{ $exam->subject->name ?? '-' }}</span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Date</span>
            <span class="detail-value">
                {{ $exam->exam_date ? \Carbon\Carbon::parse($exam->exam_date)->format('d M Y') : '-' }}
            </span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Marks</span>
            <span class="detail-value">
                Total: {{ number_format($exam->total_marks, 2) }},
                Passing: {{ number_format($exam->passing_marks, 2) }}
            </span>
        </div>
    </div>
</div>

@can('exam_result_create')
<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">Student Marks Entry</p>

        <span class="page-card-note">
            <i class="fas fa-info-circle"></i>
            Save marks to generate pass/fail, percentage and rank
        </span>
    </div>

    <div class="page-card-table">
        <form method="POST" action="{{ route('admin.exams.results.store', $exam->id) }}">
            @csrf

            <table class="min-w-full">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Code</th>
                        <th>Marks Obtained</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($students as $index => $student)
                        @php
                            $result = $existingResults[$student->id] ?? null;
                        @endphp

                        <tr>
                            <td>
                                <input type="hidden" name="results[{{ $index }}][student_id]" value="{{ $student->id }}">
                                {{ $student->user->name ?? '-' }}
                            </td>

                            <td>
                                {{ $student->student_code ?? '-' }}
                            </td>

                            <td>
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       max="{{ $exam->total_marks }}"
                                       name="results[{{ $index }}][marks_obtained]"
                                       value="{{ old('results.' . $index . '.marks_obtained', $result->marks_obtained ?? 0) }}"
                                       class="field-input"
                                       style="max-width:140px;">
                            </td>

                            <td>
                                <select name="results[{{ $index }}][result_status]" class="field-input" style="max-width:140px;">
                                    <option value="pass" {{ old('results.' . $index . '.result_status', $result->result_status ?? 'pass') == 'pass' ? 'selected' : '' }}>Present</option>
                                    <option value="absent" {{ old('results.' . $index . '.result_status', $result->result_status ?? '') == 'absent' ? 'selected' : '' }}>Absent</option>
                                </select>
                            </td>

                            <td>
                                <input type="text"
                                       name="results[{{ $index }}][remarks]"
                                       value="{{ old('results.' . $index . '.remarks', $result->remarks ?? '') }}"
                                       class="field-input"
                                       placeholder="Remarks">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="form-actions" style="padding:18px;">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i>
                    Save Results
                </button>
            </div>
        </form>
    </div>
</div>
@endcan

<div class="page-card mt-3">
    <div class="page-card-header">
        <p class="page-card-title">Result Report</p>
    </div>

    <div class="page-card-table">
        <table class="min-w-full">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Student</th>
                    <th>Marks</th>
                    <th>Percentage</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>

            <tbody>
                @forelse($exam->results->sortBy('rank') as $result)
                    <tr>
                        <td>
                            {{ $result->rank ?? '-' }}
                        </td>

                        <td>
                            {{ $result->student->user->name ?? '-' }}
                        </td>

                        <td>
                            {{ number_format($result->marks_obtained, 2) }}
                            /
                            {{ number_format($result->total_marks, 2) }}
                        </td>

                        <td>
                            {{ number_format($result->percentage, 2) }}%
                        </td>

                        <td>
                            @if($result->result_status == 'pass')
                                <span class="status-pill success">Pass</span>
                            @elseif($result->result_status == 'absent')
                                <span class="status-pill warning">Absent</span>
                            @else
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Fail</span>
                            @endif
                        </td>

                        <td>
                            {{ $result->remarks ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No result entered yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection