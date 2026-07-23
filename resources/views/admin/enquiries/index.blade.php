@extends('layouts.admin')

@section('page-title', 'Enquiries')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Enquiries</h2>
        <p class="admin-page-subtitle">
            Manage student enquiries, follow-ups, lead status and admission conversion
        </p>
    </div>

    @can('enquiry_create')
        <a href="{{ route('admin.enquiries.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Enquiry
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Enquiries</p>
        <p class="stat-value">{{ $enquiries->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">New</p>
        <p class="stat-value">{{ $enquiries->where('status', 'new')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Follow Up</p>
        <p class="stat-value">{{ $enquiries->where('status', 'follow_up')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Converted</p>
        <p class="stat-value">{{ $enquiries->where('status', 'converted')->count() }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Enquiries</p>

        <span class="page-card-note">
            <i class="fas fa-info-circle"></i>
            Track enquiry status and next follow-up date
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-Enquiry">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Phone</th>
                    <th>Branch</th>
                    <th>Course</th>
                    <th>Source</th>
                    <th>Next Follow-up</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($enquiries as $enquiry)
                    <tr data-entry-id="{{ $enquiry->id }}">
                        <td></td>

                        <td>
                            <span class="id-text">#{{ $loop->iteration }}</span>
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                                    $color = $colors[$enquiry->id % count($colors)];
                                @endphp

                                <div class="avatar-circle" style="background: {{ $color }};">
                                    {{ strtoupper(substr($enquiry->student_name, 0, 1)) }}
                                </div>

                                <div>
                                    <p class="table-main-text">{{ $enquiry->student_name }}</p>
                                    <p class="table-sub-text">
                                        {{ $enquiry->email ?? 'Enquiry Lead' }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td style="color:#475569;">
                            <div>
                                <p class="table-main-text" style="font-size:13px;">
                                    {{ $enquiry->phone }}
                                </p>
                                <p class="table-sub-text">
                                    {{ $enquiry->alternate_phone ?? '-' }}
                                </p>
                            </div>
                        </td>

                        <td>
                            @if($enquiry->branch)
                                <span class="role-tag">{{ $enquiry->branch->name }}</span>
                            @else
                                <span style="font-size:12px; color:#94A3B8;">No Branch</span>
                            @endif
                        </td>

                        <td>
                            @if($enquiry->course)
                                <span class="role-tag">{{ $enquiry->course->name }}</span>
                            @else
                                <span style="font-size:12px; color:#94A3B8;">No Course</span>
                            @endif
                        </td>

                        <td>
                            {{ $enquiry->source ?? '-' }}
                        </td>

                        <td>
                            @if($enquiry->next_follow_up_date)
                                <span class="code-pill">
                                    {{ $enquiry->next_follow_up_date->format('Y M d') }}
                                </span>
                            @else
                                <span style="font-size:12px; color:#94A3B8;">—</span>
                            @endif
                        </td>

                        <td>
                            @if($enquiry->status == 'new')
                                <span class="status-pill" style="background:#DBEAFE;color:#1D4ED8;">New</span>
                            @elseif($enquiry->status == 'follow_up')
                                <span class="status-pill warning">Follow Up</span>
                            @elseif($enquiry->status == 'interested')
                                <span class="status-pill success">Interested</span>
                            @elseif($enquiry->status == 'converted')
                                <span class="status-pill" style="background:#DCFCE7;color:#166534;">Converted</span>
                            @elseif($enquiry->status == 'not_interested')
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Not Interested</span>
                            @else
                                <span class="status-pill" style="background:#F1F5F9;color:#475569;">Rejected</span>
                            @endif
                        </td>

                        <td>
                            <div class="action-row">
                                @can('enquiry_show')
                                    <a href="{{ route('admin.enquiries.show', $enquiry->id) }}" class="btn-outline">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                @endcan

                                @can('enquiry_edit')
                                    <a href="{{ route('admin.enquiries.edit', $enquiry->id) }}" class="btn-outline btn-outline-edit">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('enquiry_delete')
                                    <form action="{{ route('admin.enquiries.destroy', $enquiry->id) }}"
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
    initAdminDataTable('.datatable-Enquiry', {
        canDelete: @can('enquiry_delete') true @else false @endcan,
        massDeleteUrl: "{{ route('admin.enquiries.massDestroy') }}",
        deleteText: "{{ trans('global.datatables.delete') }}",
        zeroSelectedText: "{{ trans('global.datatables.zero_selected') }}",
        confirmText: "{{ trans('global.areYouSure') }}",
        searchPlaceholder: 'Search enquiries...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ enquiries'
    });
});
</script>
@endsection