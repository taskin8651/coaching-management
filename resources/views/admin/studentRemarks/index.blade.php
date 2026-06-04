@extends('layouts.admin')

@section('page-title', 'Student Remarks')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Student Remarks</h2>
        <p class="admin-page-subtitle">
            Positive, negative and academic remark history for students
        </p>
    </div>

    @can('student_remark_create')
        <a href="{{ route('admin.student-remarks.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Remark
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Remarks</p>
        <p class="stat-value">{{ $remarks->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Positive</p>
        <p class="stat-value">{{ $remarks->where('remark_type', 'positive')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Negative</p>
        <p class="stat-value">{{ $remarks->where('remark_type', 'negative')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Parent Visible</p>
        <p class="stat-value">{{ $remarks->where('visible_to_parent', 1)->count() }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Student Remarks</p>

        <span class="page-card-note">
            <i class="fas fa-comment-dots"></i>
            Manage student feedback and parent visibility
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-Remarks">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Student</th>
                    <th>Type</th>
                    <th>Title</th>
                    <th>Remark</th>
                    <th>Parent</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($remarks as $item)
                    <tr>
                        <td>
                            {{ $item->remark_date ? \Carbon\Carbon::parse($item->remark_date)->format('d M Y') : '-' }}
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $name = $item->student->user->name ?? 'Student';
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
                            @if($item->remark_type == 'positive')
                                <span class="status-pill success">Positive</span>
                            @elseif($item->remark_type == 'negative')
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Negative</span>
                            @elseif($item->remark_type == 'academic')
                                <span class="status-pill" style="background:#DBEAFE;color:#1D4ED8;">Academic</span>
                            @elseif($item->remark_type == 'discipline')
                                <span class="status-pill warning">Discipline</span>
                            @else
                                <span class="status-pill" style="background:#F1F5F9;color:#475569;">
                                    {{ ucfirst($item->remark_type ?? '-') }}
                                </span>
                            @endif
                        </td>

                        <td>
                            <p class="table-main-text">{{ $item->title ?? '-' }}</p>
                            <p class="table-sub-text">Student feedback</p>
                        </td>

                        <td>
                            <p class="table-sub-text" style="max-width:360px;">
                                {{ $item->remark ? \Illuminate\Support\Str::limit($item->remark, 90) : '-' }}
                            </p>
                        </td>

                        <td>
                            @if($item->visible_to_parent)
                                <span class="status-pill success">Visible</span>
                            @else
                                <span class="status-pill warning">Hidden</span>
                            @endif
                        </td>

                        <td>
                            <div class="action-row">
                                @can('student_remark_show')
                                    <a href="{{ route('admin.student-remarks.show', $item->id) }}" class="btn-outline">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                @endcan

                                @can('student_remark_edit')
                                    <a href="{{ route('admin.student-remarks.edit', $item->id) }}" class="btn-outline btn-outline-edit">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('student_remark_delete')
                                    <form action="{{ route('admin.student-remarks.destroy', $item->id) }}"
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
    initAdminDataTable('.datatable-Remarks', {
        searchPlaceholder: 'Search remarks...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ remarks'
    });
});
</script>
@endsection