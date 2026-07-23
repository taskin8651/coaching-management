@extends('layouts.admin')

@section('page-title', 'Student Batches')

@section('content')

@php
    $grouped = $studentBatches->groupBy('student_id');
@endphp

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Student Batches</h2>
        <p class="admin-page-subtitle">
            Har student ke saare batch aur subject assignments ek hi row me
        </p>
    </div>

    @can('student_batch_create')
        <a href="{{ route('admin.student-batches.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Assign Batch
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Students</p>
        <p class="stat-value">{{ $grouped->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Total Assignments</p>
        <p class="stat-value">{{ $studentBatches->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Active</p>
        <p class="stat-value">{{ $studentBatches->where('status', 'active')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Inactive</p>
        <p class="stat-value">{{ $studentBatches->where('status', 'inactive')->count() }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Students</p>

        <span class="page-card-note">
            <i class="fas fa-users"></i>
            Ek student = ek row, saare batch/subject ek jagah
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-StudentBatches">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Batches &amp; Subjects</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($grouped as $studentId => $rows)
                    @php
                        $first = $rows->first();
                        $name = $first->student->user->name ?? $first->student->student_code ?? 'Student';
                        $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                        $color = $colors[$loop->index % count($colors)];
                        $activeCount = $rows->where('status', 'active')->count();
                        $inactiveCount = $rows->count() - $activeCount;
                        $manageRowId = $first->id;
                    @endphp

                    <tr data-entry-id="{{ $studentId }}">
                        <td>
                            <div class="inline-flex-center">
                                <div class="avatar-circle" style="background: {{ $color }};">
                                    {{ strtoupper(substr($name, 0, 1)) }}
                                </div>

                                <div>
                                    <p class="table-main-text">{{ $name }}</p>
                                    <p class="table-sub-text">{{ $first->student->student_code ?? 'Student #'.$studentId }}</p>
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="tag-wrap">
                                @foreach($rows->groupBy('batch_id') as $batchId => $batchRows)
                                    @php
                                        $subjectNames = $batchRows->pluck('subject.name')->filter()->values();
                                        $subjectLabel = $subjectNames->count() === 1 ? 'subject' : 'subjects';
                                    @endphp

                                    <span class="role-tag" title="{{ $subjectNames->implode(', ') ?: 'No subject linked' }}">
                                        {{ $batchRows->first()->batch->name ?? '-' }}
                                        @if($subjectNames->count())
                                            <span style="opacity:.7;margin-left:4px;">· {{ $subjectNames->count() }} {{ $subjectLabel }}</span>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        </td>

                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @if($activeCount)
                                    <span class="status-pill success">{{ $activeCount }} Active</span>
                                @endif

                                @if($inactiveCount)
                                    <span class="status-pill warning">{{ $inactiveCount }} Inactive</span>
                                @endif
                            </div>
                        </td>

                        <td>
                            <div class="action-row">
                                @can('student_batch_edit')
                                    <a class="btn-outline btn-outline-edit" href="{{ route('admin.student-batches.edit', $manageRowId) }}">
                                        <i class="fas fa-pencil-alt"></i>
                                        Manage
                                    </a>
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
    initAdminDataTable('.datatable-StudentBatches', {
        searchPlaceholder: 'Search students...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ students',
        order: [[0, 'asc']]
    });
});
</script>
@endsection
