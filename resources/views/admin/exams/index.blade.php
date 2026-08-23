@extends('layouts.admin')

@section('page-title', 'Exams / Tests')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Exams / Tests</h2>
        <p class="admin-page-subtitle">
            Manage branch-wise, course-wise, batch-wise exams and student results
        </p>
    </div>

    @can('exam_create')
        <a href="{{ route('admin.exams.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Exam
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Exams</p>
        <p class="stat-value">{{ $exams->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Scheduled</p>
        <p class="stat-value">{{ $exams->where('display_status', 'scheduled')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Completed</p>
        <p class="stat-value">{{ $exams->where('display_status', 'completed')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Cancelled</p>
        <p class="stat-value">{{ $exams->where('display_status', 'cancelled')->count() }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Exams</p>

        <span class="page-card-note">
            <i class="fas fa-info-circle"></i>
            Use View button to enter student marks
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-Exam">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Exam</th>
                    <th>Branch</th>
                    <th>Course</th>
                    <th>Batch</th>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Marks</th>
                    <th>Status</th>
                    <th>Remarks</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($exams as $exam)
                    <tr data-entry-id="{{ $exam->id }}">
                        <td></td>

                        <td>
                            <span class="id-text">#{{ $loop->iteration }}</span>
                        </td>

                        <td>
                            <div>
                                <p class="table-main-text">{{ $exam->title }}</p>
                                <p class="table-sub-text">{{ $exam->exam_type ?? 'Exam/Test' }}</p>
                            </div>
                        </td>

                        <td>{{ $exam->branch->name ?? '-' }}</td>
                        <td>{{ $exam->course->name ?? '-' }}</td>
                        <td>{{ $exam->batch->name ?? '-' }}</td>
                        <td>{{ $exam->subject->name ?? '-' }}</td>

                        <td>
                            {{ $exam->exam_date ? \Carbon\Carbon::parse($exam->exam_date)->format('d M Y') : '-' }}
                        </td>

                        <td>
                            {{ number_format($exam->total_marks, 0) }}
                        </td>

                        <td>
                            @if($exam->display_status == 'scheduled')
                                <span class="status-pill warning">Scheduled</span>
                            @elseif($exam->display_status == 'completed')
                                <span class="status-pill success">Completed</span>
                            @else
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Cancelled</span>
                            @endif
                        </td>

                        <td>
                            @if($exam->results_count > 0)
                                <span class="status-pill success">
                                    <i class="fas fa-check-circle"></i>
                                    Remark Added
                                </span>
                            @else
                                <span class="status-pill warning">
                                    <i class="fas fa-clock"></i>
                                    Pending
                                </span>
                            @endif
                        </td>

                        <td>
                            <div class="action-row">
                                @can('exam_show')
                                    <a href="{{ route('admin.exams.show', $exam->id) }}" class="btn-outline">
                                        <i class="fas fa-eye"></i>
                                        View / Result
                                    </a>
                                @endcan

                                @can('exam_edit')
                                    <a href="{{ route('admin.exams.edit', $exam->id) }}" class="btn-outline btn-outline-edit">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('exam_delete')
                                    <form action="{{ route('admin.exams.destroy', $exam->id) }}"
                                          method="POST"
                                          style="display:inline;"
                                          onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
                                        @method('DELETE')
                                        @csrf

                                        <button type="submit" class="btn-outline btn-outline-danger">
                                            <i class="fas fa-trash-alt"></i>
                                            Delete
                                        </button>
                                    </form>
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
    initAdminDataTable('.datatable-Exam', {
        canDelete: @can('exam_delete') true @else false @endcan,
        massDeleteUrl: "{{ route('admin.exams.massDestroy') }}",
        deleteText: "{{ trans('global.datatables.delete') }}",
        zeroSelectedText: "{{ trans('global.datatables.zero_selected') }}",
        confirmText: "{{ trans('global.areYouSure') }}",
        searchPlaceholder: 'Search exams...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ exams'
    });
});
</script>
@endsection