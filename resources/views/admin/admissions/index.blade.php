@extends('layouts.admin')

@section('page-title', 'Admissions')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Admissions</h2>
        <p class="admin-page-subtitle">
            Manage student admissions, guardian details, fee snapshot and documents
        </p>
    </div>

    @can('admission_create')
        <a href="{{ route('admin.admissions.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Admission
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Admissions</p>
        <p class="stat-value">{{ $admissions->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Confirmed</p>
        <p class="stat-value">{{ $admissions->where('status', 'confirmed')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Pending</p>
        <p class="stat-value">{{ $admissions->where('status', 'pending')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Payable Amount</p>
        <p class="stat-value">₹{{ number_format($admissions->sum('payable_amount'), 0) }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Admissions</p>

        <span class="page-card-note">
            <i class="fas fa-info-circle"></i>
            Role-wise admission records
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-Admission">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Admission</th>
                    <th>Student</th>
                    <th>Branch</th>
                    <th>Course</th>
                    <th>Batch</th>
                    <th>Payable</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($admissions as $admission)
                    <tr data-entry-id="{{ $admission->id }}">
                        <td></td>

                        <td>
                            <span class="id-text">#{{ $loop->iteration }}</span>
                        </td>

                        <td>
                            <span class="code-pill">{{ $admission->admission_no ?? '-' }}</span>
                            <p class="table-sub-text">
                                {{ $admission->admission_date ? \Carbon\Carbon::parse($admission->admission_date)->format('d M Y') : '-' }}
                            </p>
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $name = $admission->student->user->name ?? 'Student';
                                    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                                    $color = $colors[$admission->id % count($colors)];
                                @endphp

                                <div class="avatar-circle" style="background: {{ $color }};">
                                    {{ strtoupper(substr($name, 0, 1)) }}
                                </div>

                                <div>
                                    <p class="table-main-text">{{ $name }}</p>
                                    <p class="table-sub-text">
                                        {{ $admission->student->student_code ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td>{{ $admission->branch->name ?? '-' }}</td>
                        <td>{{ $admission->course->name ?? '-' }}</td>
                        <td>{{ $admission->batch->name ?? '-' }}</td>

                        <td>
                            <strong>₹{{ number_format($admission->payable_amount, 2) }}</strong>
                        </td>

                        <td>
                            @if($admission->status == 'confirmed')
                                <span class="status-pill success">Confirmed</span>
                            @elseif($admission->status == 'pending')
                                <span class="status-pill warning">Pending</span>
                            @elseif($admission->status == 'rejected')
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Rejected</span>
                            @elseif($admission->status == 'cancelled')
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Cancelled</span>
                            @else
                                <span class="status-pill" style="background:#E0F2FE;color:#075985;">Completed</span>
                            @endif
                        </td>

                        <td>
                            <div class="action-row">
                                @can('admission_show')
                                    <a href="{{ route('admin.admissions.show', $admission->id) }}" class="btn-outline">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                @endcan

                                @can('admission_edit')
                                    <a href="{{ route('admin.admissions.edit', $admission->id) }}" class="btn-outline btn-outline-edit">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('admission_delete')
                                    <form action="{{ route('admin.admissions.destroy', $admission->id) }}"
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
    initAdminDataTable('.datatable-Admission', {
        canDelete: @can('admission_delete') true @else false @endcan,
        massDeleteUrl: "{{ route('admin.admissions.massDestroy') }}",
        deleteText: "{{ trans('global.datatables.delete') }}",
        zeroSelectedText: "{{ trans('global.datatables.zero_selected') }}",
        confirmText: "{{ trans('global.areYouSure') }}",
        searchPlaceholder: 'Search admissions...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ admissions'
    });
});
</script>
@endsection