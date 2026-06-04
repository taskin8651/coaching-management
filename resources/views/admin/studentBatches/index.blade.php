@extends('layouts.admin')

@section('page-title', 'Student Batches')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Student Batches</h2>
        <p class="admin-page-subtitle">
            Multiple active batch and subject assignments per student
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

    <div class="stat-card">
        <p class="stat-label">Completed</p>
        <p class="stat-value">{{ $studentBatches->where('status', 'completed')->count() }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Student Batch Assignments</p>

        <span class="page-card-note">
            <i class="fas fa-users"></i>
            Manage student multiple batch mapping
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-StudentBatches">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Batch</th>
                    <th>Subject</th>
                    <th>Dates</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($studentBatches as $item)
                    <tr data-entry-id="{{ $item->id }}">
                        <td>
                            <span class="id-text">#{{ $item->id }}</span>
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $name = $item->student->user->name ?? $item->student->student_code ?? 'Student';
                                    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                                    $color = $colors[$loop->index % count($colors)];
                                @endphp

                                <div class="avatar-circle" style="background: {{ $color }};">
                                    {{ strtoupper(substr($name, 0, 1)) }}
                                </div>

                                <div>
                                    <p class="table-main-text">{{ $name }}</p>
                                    <p class="table-sub-text">{{ $item->student->student_code ?? 'Student' }}</p>
                                </div>
                            </div>
                        </td>

                        <td>
                            <p class="table-main-text">{{ $item->batch->name ?? '-' }}</p>
                            <p class="table-sub-text">Batch</p>
                        </td>

                        <td>
                            @if($item->subject)
                                <p class="table-main-text">{{ $item->subject->name ?? '-' }}</p>
                                <p class="table-sub-text">Subject</p>
                            @else
                                <span style="font-size:12px;color:#94A3B8;">—</span>
                            @endif
                        </td>

                        <td>
                            <p class="table-main-text">
                                {{ $item->start_date ? \Carbon\Carbon::parse($item->start_date)->format('d M Y') : '-' }}
                            </p>

                            <p class="table-sub-text">
                                To:
                                {{ $item->end_date ? \Carbon\Carbon::parse($item->end_date)->format('d M Y') : 'Active' }}
                            </p>
                        </td>

                        <td>
                            @if($item->status === 'active')
                                <span class="status-pill success">Active</span>
                            @elseif($item->status === 'completed')
                                <span class="status-pill success">Completed</span>
                            @elseif($item->status === 'inactive')
                                <span class="status-pill warning">Inactive</span>
                            @elseif($item->status === 'cancelled')
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Cancelled</span>
                            @else
                                <span class="status-pill" style="background:#F1F5F9;color:#475569;">
                                    {{ ucfirst($item->status ?? '-') }}
                                </span>
                            @endif
                        </td>

                        <td>
                            <div class="action-row">
                                @can('student_batch_show')
                                    <a class="btn-outline" href="{{ route('admin.student-batches.show', $item->id) }}">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                @endcan

                                @can('student_batch_edit')
                                    <a class="btn-outline btn-outline-edit" href="{{ route('admin.student-batches.edit', $item->id) }}">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('student_batch_delete')
                                    <form method="POST"
                                          action="{{ route('admin.student-batches.destroy', $item->id) }}"
                                          style="display:inline;"
                                          onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
                                        @csrf
                                        @method('DELETE')

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
    initAdminDataTable('.datatable-StudentBatches', {
        searchPlaceholder: 'Search student batches...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ student batch assignments'
    });
});
</script>
@endsection