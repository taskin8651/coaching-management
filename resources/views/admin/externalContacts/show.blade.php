@extends('layouts.admin')

@section('page-title', 'External Contact')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.external-contacts.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">{{ $externalContact->name }}</h2>
        <p class="admin-page-subtitle">External contact details and event participation history</p>
    </div>

    <div class="show-actions">
        @can('external_contact_edit')
            <a href="{{ route('admin.external-contacts.edit', $externalContact->id) }}" class="btn-primary">
                <i class="fas fa-pencil-alt"></i> Edit Contact
            </a>
        @endcan
    </div>
</div>

<div class="show-grid">
    <div>
        <div class="detail-card mb-3">
            <div class="profile-hero">
                <div class="profile-avatar-lg" style="background:#8B5CF6;">
                    {{ strtoupper(substr($externalContact->name, 0, 1)) }}
                </div>
                <p class="profile-title">{{ $externalContact->name }}</p>
                <p class="profile-subtitle">{{ $externalContact->mobile }}</p>
            </div>
        </div>

        <div class="detail-card detail-card-pad">
            <p class="quick-title">Quick Actions</p>
            <div class="quick-list">
                <a href="{{ route('admin.external-contacts.index') }}" class="quick-link"><i class="fas fa-list"></i> All Contacts</a>
                @can('external_contact_create')
                    <a href="{{ route('admin.external-contacts.create') }}" class="quick-link"><i class="fas fa-plus"></i> Add New Contact</a>
                @endcan
            </div>
        </div>
    </div>

    <div>
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-info-circle"></i></div>
                <p class="detail-section-title">Contact Information</p>
            </div>
            <div class="detail-section-body">
                <div class="detail-row"><span class="detail-label">Mobile</span><span class="detail-value">{{ $externalContact->mobile }}</span></div>
                <div class="detail-row"><span class="detail-label">WhatsApp</span><span class="detail-value">{{ $externalContact->whatsapp_number ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Email</span><span class="detail-value">{{ $externalContact->email ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Standard</span><span class="detail-value">{{ $externalContact->standard ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">School</span><span class="detail-value">{{ $externalContact->school_name ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">City / Area</span><span class="detail-value">{{ $externalContact->city ?? '-' }} {{ $externalContact->area ? '/ ' . $externalContact->area : '' }}</span></div>
                <div class="detail-row"><span class="detail-label">Guardian</span><span class="detail-value">{{ $externalContact->guardian_name ?? '-' }} {{ $externalContact->guardian_mobile ? '(' . $externalContact->guardian_mobile . ')' : '' }}</span></div>
                <div class="detail-row"><span class="detail-label">Remarks</span><span class="detail-value">{{ $externalContact->remarks ?? '-' }}</span></div>
            </div>
        </div>

        <div class="detail-card">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-calendar-check"></i></div>
                <p class="detail-section-title">Event Participation History</p>
            </div>

            <div class="page-card-table">
                <table class="min-w-full">
                    <thead>
                        <tr><th>Event</th><th>Enrolled</th><th>Fee</th><th>Payment</th><th>Attendance</th><th>Certificate</th></tr>
                    </thead>
                    <tbody>
                        @forelse($externalContact->enrollments as $enrollment)
                            <tr>
                                <td>
                                    @can('event_show')
                                        <a href="{{ route('admin.events.show', $enrollment->event_id) }}">{{ $enrollment->event->name ?? '-' }}</a>
                                    @else
                                        {{ $enrollment->event->name ?? '-' }}
                                    @endcan
                                </td>
                                <td>{{ optional($enrollment->enrollment_date)->format('d M Y') }}</td>
                                <td>₹{{ number_format($enrollment->fee_amount, 2) }}</td>
                                <td>{{ ucfirst($enrollment->payment_status) }}</td>
                                <td>{{ is_null($enrollment->is_present) ? 'Not marked' : ($enrollment->is_present ? 'Present' : 'Absent') }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $enrollment->certificate_status)) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6">No event participation yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
