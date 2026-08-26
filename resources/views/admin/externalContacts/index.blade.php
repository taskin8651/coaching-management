@extends('layouts.admin')

@section('page-title', 'External Contacts')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">External Contacts</h2>
        <p class="admin-page-subtitle">
            Non-Karmayoga participants who registered for events — reusable across future events, never turned into fake student admissions
        </p>
    </div>

    @can('external_contact_create')
        <a href="{{ route('admin.external-contacts.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Contact
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Contacts</p>
        <p class="stat-value">{{ $externalContacts->count() }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">With Event History</p>
        <p class="stat-value">{{ $externalContacts->where('enrollments_count', '>', 0)->count() }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All External Contacts</p>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-ExternalContact">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>School</th>
                    <th>City</th>
                    <th>Events</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($externalContacts as $contact)
                    <tr data-entry-id="{{ $contact->id }}">
                        <td></td>
                        <td><span class="id-text">#{{ $loop->iteration }}</span></td>
                        <td>
                            <p class="table-main-text">{{ $contact->name }}</p>
                            <p class="table-sub-text">{{ $contact->standard ?? '-' }}</p>
                        </td>
                        <td>{{ $contact->mobile }}</td>
                        <td>{{ $contact->school_name ?? '-' }}</td>
                        <td>{{ $contact->city ?? '-' }}</td>
                        <td>{{ $contact->enrollments_count }}</td>
                        <td>
                            <div class="action-row">
                                @can('external_contact_show')
                                    <a href="{{ route('admin.external-contacts.show', $contact->id) }}" class="btn-outline"><i class="fas fa-eye"></i> View</a>
                                @endcan
                                @can('external_contact_edit')
                                    <a href="{{ route('admin.external-contacts.edit', $contact->id) }}" class="btn-outline btn-outline-edit"><i class="fas fa-pencil-alt"></i> Edit</a>
                                @endcan
                                @can('external_contact_delete')
                                    <form action="{{ route('admin.external-contacts.destroy', $contact->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
                                        @method('DELETE')
                                        @csrf
                                        <button type="submit" class="btn-outline btn-outline-danger"><i class="fas fa-trash-alt"></i> Delete</button>
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
    initAdminDataTable('.datatable-ExternalContact', {
        canDelete: @can('external_contact_delete') true @else false @endcan,
        massDeleteUrl: "{{ route('admin.external-contacts.massDestroy') }}",
        deleteText: "{{ trans('global.datatables.delete') }}",
        zeroSelectedText: "{{ trans('global.datatables.zero_selected') }}",
        confirmText: "{{ trans('global.areYouSure') }}",
        searchPlaceholder: 'Search contacts...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ contacts'
    });
});
</script>
@endsection
