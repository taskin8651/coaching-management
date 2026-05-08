@extends('layouts.admin')

@section('page-title', 'Fee Structures')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Fee Structures</h2>
        <p class="admin-page-subtitle">
            Manage branch, course and batch wise fee setup
        </p>
    </div>

    @can('fee_structure_create')
        <a href="{{ route('admin.fee-structures.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Fee Structure
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Structures</p>
        <p class="stat-value">{{ $feeStructures->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Active</p>
        <p class="stat-value">{{ $feeStructures->where('status', 'active')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Inactive</p>
        <p class="stat-value">{{ $feeStructures->where('status', 'inactive')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Total Fee Value</p>
        <p class="stat-value">₹{{ number_format($feeStructures->sum('total_fee'), 0) }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Fee Structures</p>

        <span class="page-card-note">
            <i class="fas fa-info-circle"></i>
            Fee setup is used for admission and student fee plans
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-FeeStructure">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Branch</th>
                    <th>Course</th>
                    <th>Batch</th>
                    <th>Total Fee</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($feeStructures as $feeStructure)
                    <tr data-entry-id="{{ $feeStructure->id }}">
                        <td></td>

                        <td>
                            <span class="id-text">#{{ $feeStructure->id }}</span>
                        </td>

                        <td>
                            <p class="table-main-text">{{ $feeStructure->title }}</p>
                            <p class="table-sub-text">
                                Admission: ₹{{ number_format($feeStructure->admission_fee, 0) }}
                                |
                                Tuition: ₹{{ number_format($feeStructure->tuition_fee, 0) }}
                            </p>
                        </td>

                        <td>{{ $feeStructure->branch->name ?? '-' }}</td>
                        <td>{{ $feeStructure->course->name ?? '-' }}</td>
                        <td>{{ $feeStructure->batch->name ?? 'All Batches' }}</td>

                        <td>
                            <strong>₹{{ number_format($feeStructure->total_fee, 2) }}</strong>
                        </td>

                        <td>
                            @if($feeStructure->status == 'active')
                                <span class="status-pill success">Active</span>
                            @else
                                <span class="status-pill warning">Inactive</span>
                            @endif
                        </td>

                        <td>
                            <div class="action-row">
                                @can('fee_structure_show')
                                    <a href="{{ route('admin.fee-structures.show', $feeStructure->id) }}" class="btn-outline">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                @endcan

                                @can('fee_structure_edit')
                                    <a href="{{ route('admin.fee-structures.edit', $feeStructure->id) }}" class="btn-outline btn-outline-edit">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('fee_structure_delete')
                                    <form action="{{ route('admin.fee-structures.destroy', $feeStructure->id) }}"
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
    initAdminDataTable('.datatable-FeeStructure', {
        canDelete: @can('fee_structure_delete') true @else false @endcan,
        massDeleteUrl: "{{ route('admin.fee-structures.massDestroy') }}",
        deleteText: "{{ trans('global.datatables.delete') }}",
        zeroSelectedText: "{{ trans('global.datatables.zero_selected') }}",
        confirmText: "{{ trans('global.areYouSure') }}",
        searchPlaceholder: 'Search fee structures...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ fee structures'
    });
});
</script>
@endsection