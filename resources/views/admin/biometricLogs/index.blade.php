@extends('layouts.admin')

@section('page-title', 'Biometric Logs')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Biometric Logs</h2>
        <p class="admin-page-subtitle">
            Raw biometric punch logs, device details and processing status
        </p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Logs</p>
        <p class="stat-value">{{ $logs->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Processed</p>
        <p class="stat-value">{{ $logs->where('processed_status', 'processed')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Pending</p>
        <p class="stat-value">{{ $logs->where('processed_status', 'pending')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Failed</p>
        <p class="stat-value">{{ $logs->where('processed_status', 'failed')->count() }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Biometric Logs</p>

        <span class="page-card-note">
            <i class="fas fa-fingerprint"></i>
            Raw punch data from biometric devices
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-BiometricLogs">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Biometric User</th>
                    <th>User Type</th>
                    <th>Punch Details</th>
                    <th>Device</th>
                    <th>Status</th>
                    <th>Message</th>
                </tr>
            </thead>

            <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td>
                            <span class="id-text">#{{ $log->id }}</span>
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                                    $color  = $colors[$loop->index % count($colors)];
                                    $bioId  = $log->biometric_user_id ?? 'BIO';
                                @endphp

                                <div class="avatar-circle" style="background: {{ $color }};">
                                    <i class="fas fa-fingerprint"></i>
                                </div>

                                <div>
                                    <p class="table-main-text">{{ $bioId }}</p>
                                    <p class="table-sub-text">Biometric ID</p>
                                </div>
                            </div>
                        </td>

                        <td>
                            @if($log->user_type)
                                <span class="code-pill">{{ ucfirst($log->user_type) }}</span>
                            @else
                                <span style="font-size:12px;color:#94A3B8;">—</span>
                            @endif
                        </td>

                        <td>
                            <p class="table-main-text">
                                {{ $log->punch_time ? \Carbon\Carbon::parse($log->punch_time)->format('d M Y h:i A') : '-' }}
                            </p>

                            <p class="table-sub-text">
                                Punch Type:
                                <strong>{{ $log->punch_type ? strtoupper($log->punch_type) : '-' }}</strong>
                            </p>
                        </td>

                        <td>
                            <span class="code-pill">
                                {{ $log->device_id ?? '-' }}
                            </span>
                        </td>

                        <td>
                            @if($log->processed_status === 'processed')
                                <span class="status-pill success">Processed</span>
                            @elseif($log->processed_status === 'failed')
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Failed</span>
                            @elseif($log->processed_status === 'pending')
                                <span class="status-pill warning">Pending</span>
                            @else
                                <span class="status-pill" style="background:#F1F5F9;color:#475569;">
                                    {{ ucfirst($log->processed_status ?? '-') }}
                                </span>
                            @endif
                        </td>

                        <td>
                            <p class="table-sub-text" style="max-width:280px;">
                                {{ $log->processing_message ?? '-' }}
                            </p>
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
    initAdminDataTable('.datatable-BiometricLogs', {
        searchPlaceholder: 'Search biometric logs...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ biometric logs'
    });
});
</script>
@endsection