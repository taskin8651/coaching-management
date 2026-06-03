@extends('layouts.admin')

@section('page-title', 'WhatsApp Logs')

@section('content')
<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">WhatsApp Logs</h2>
        <p class="admin-page-subtitle">Guardian notification delivery history</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-table">
        <table class="min-w-full datatable datatable-WhatsappLogs">
            <thead>
                <tr><th>ID</th><th>Student</th><th>Guardian</th><th>Module</th><th>Message</th><th>Status</th><th>Sent At</th></tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td>#{{ $log->id }}</td>
                        <td>{{ $log->student->user->name ?? '-' }}</td>
                        <td>{{ $log->guardian_number ?? '-' }}</td>
                        <td>{{ $log->module_name ?? '-' }}</td>
                        <td>{{ $log->message }}</td>
                        <td><span class="status-pill {{ $log->status === 'sent' ? 'success' : ($log->status === 'failed' ? 'danger' : 'warning') }}">{{ ucfirst($log->status) }}</span></td>
                        <td>{{ $log->sent_at ? $log->sent_at->format('d M Y h:i A') : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>$(function(){ initAdminDataTable('.datatable-WhatsappLogs', { searchPlaceholder: 'Search WhatsApp logs...' }); });</script>
@endsection
