@extends('layouts.admin')

@section('page-title', 'Academic Years')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Academic Years</h2>
        <p class="admin-page-subtitle">Manage academic year options used in fee structures</p>
    </div>

    @can('fee_master_create')
        <a href="{{ route('admin.academic-years.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Academic Year
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Years</p>
        <p class="stat-value">{{ $academicYears->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Active</p>
        <p class="stat-value">{{ $academicYears->where('status', 'active')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Current</p>
        <p class="stat-value">{{ $academicYears->where('is_current', true)->count() }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Academic Years</p>

        <span class="page-card-note">
            <i class="fas fa-calendar-alt"></i>
            Active years appear in fee structure forms
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-AcademicYear">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Academic Year</th>
                    <th>Dates</th>
                    <th>Current</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($academicYears as $academicYear)
                    <tr data-entry-id="{{ $academicYear->id }}">
                        <td></td>

                        <td><span class="id-text">#{{ $loop->iteration }}</span></td>

                        <td>
                            <p class="table-main-text">{{ $academicYear->name }}</p>
                        </td>

                        <td>
                            {{ optional($academicYear->start_date)->format('d M Y') ?? '-' }}
                            -
                            {{ optional($academicYear->end_date)->format('d M Y') ?? '-' }}
                        </td>

                        <td>
                            @if($academicYear->is_current)
                                <span class="status-pill success">Current</span>
                            @else
                                <span class="status-pill">No</span>
                            @endif
                        </td>

                        <td>
                            @if($academicYear->status === 'active')
                                <span class="status-pill success">Active</span>
                            @else
                                <span class="status-pill warning">Inactive</span>
                            @endif
                        </td>

                        <td>
                            <div class="action-row">
                                @can('fee_master_edit')
                                    <a href="{{ route('admin.academic-years.edit', $academicYear->id) }}" class="btn-outline btn-outline-edit">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('fee_master_delete')
                                    <form action="{{ route('admin.academic-years.destroy', $academicYear->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
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
    initAdminDataTable('.datatable-AcademicYear', {
        canDelete: @can('fee_master_delete') true @else false @endcan,
        massDeleteUrl: "{{ route('admin.academic-years.massDestroy') }}",
        deleteText: "{{ trans('global.datatables.delete') }}",
        zeroSelectedText: "{{ trans('global.datatables.zero_selected') }}",
        confirmText: "{{ trans('global.areYouSure') }}",
        searchPlaceholder: 'Search academic years...',
        infoText: 'Showing _START_-_END_ of _TOTAL_ academic years'
    });
});
</script>
@endsection
