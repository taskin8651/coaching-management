@extends('layouts.admin')

@section('page-title', 'Biometric Logs')

@section('content')
<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Biometric Logs</h2>
        <p class="admin-page-subtitle">Raw punch logs and processing status</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-table">
        <table class="min-w-full datatable datatable-BiometricLogs">
            <thead>
                <tr>
                    <th>ID</th><th>Biometric ID</th><th>User Type</th><th>Punch</th><th>Device</th><th>Status</th><th>Message</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td>#{{ $log->id }}</td>
                        <td>{{ $log->biometric_user_id }}</td>
                        <td>{{ ucfirst($log->user_type) }}</td>
                        <td>{{ $log->punch_time ? $log->punch_time->format('d M Y h:i A') : '-' }} / {{ strtoupper($log->punch_type) }}</td>
                        <td>{{ $log->device_id ?? '-' }}</td>
                        <td><span class="status-pill {{ $log->processed_status === 'processed' ? 'success' : ($log->processed_status === 'failed' ? 'danger' : 'warning') }}">{{ ucfirst($log->processed_status) }}</span></td>
                        <td>{{ $log->processing_message ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>$(function(){ initAdminDataTable('.datatable-BiometricLogs', { searchPlaceholder: 'Search biometric logs...' }); });</script>
@endsection
