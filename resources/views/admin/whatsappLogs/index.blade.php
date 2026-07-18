@extends('layouts.admin')

@section('page-title', 'WhatsApp Logs')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">WhatsApp Logs</h2>
        <p class="admin-page-subtitle">
            Guardian notification delivery history, message status and sent time
        </p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Logs</p>
        <p class="stat-value">{{ $logs->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Sent</p>
        <p class="stat-value">{{ $logs->where('status', 'sent')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Failed</p>
        <p class="stat-value">{{ $logs->where('status', 'failed')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Pending</p>
        <p class="stat-value">{{ $logs->where('status', 'pending')->count() }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All WhatsApp Logs</p>

        <span class="page-card-note">
            <i class="fab fa-whatsapp"></i>
            Track guardian alert delivery status
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-WhatsappLogs">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Guardian</th>
                    <th>Module</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Sent At</th>
                </tr>
            </thead>

            <tbody>
                @foreach($logs as $log)
                    <tr data-entry-id="{{ $log->id }}">
                        <td>
                            <span class="id-text">#{{ $loop->iteration }}</span>
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $name = $log->student->user->name ?? 'Student';
                                    $colors = ['#25D366','#0EA5E9','#10B981','#F59E0B','#4F46E5','#8B5CF6','#EC4899','#14B8A6'];
                                    $color = $colors[$loop->index % count($colors)];
                                @endphp

                                <div class="avatar-circle" style="background: {{ $color }};">
                                    {{ strtoupper(substr($name, 0, 1)) }}
                                </div>

                                <div>
                                    <p class="table-main-text">{{ $name }}</p>
                                    <p class="table-sub-text">{{ $log->student->student_code ?? 'Student' }}</p>
                                </div>
                            </div>
                        </td>

                        <td>
                            @if($log->guardian_number)
                                <span class="code-pill">
                                    {{ $log->guardian_number }}
                                </span>
                            @else
                                <span style="font-size:12px;color:#94A3B8;">—</span>
                            @endif
                        </td>

                        <td>
                            @if($log->module_name)
                                <span class="status-pill" style="background:#EDE9FE;color:#6D28D9;">
                                    {{ ucfirst(str_replace('_', ' ', $log->module_name)) }}
                                </span>
                            @else
                                <span style="font-size:12px;color:#94A3B8;">—</span>
                            @endif
                        </td>

                        <td>
                            <p class="table-main-text" style="max-width:420px;">
                                {{ $log->message ? \Illuminate\Support\Str::limit($log->message, 90) : '-' }}
                            </p>
                            <p class="table-sub-text">Guardian Alert Message</p>
                        </td>

                        <td>
                            @if($log->status === 'sent')
                                <span class="status-pill success">
                                    <i class="fas fa-check"></i>
                                    Sent
                                </span>
                            @elseif($log->status === 'failed')
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">
                                    <i class="fas fa-times"></i>
                                    Failed
                                </span>
                            @elseif($log->status === 'pending')
                                <span class="status-pill warning">
                                    <i class="fas fa-clock"></i>
                                    Pending
                                </span>
                            @else
                                <span class="status-pill" style="background:#F1F5F9;color:#475569;">
                                    {{ ucfirst($log->status ?? '-') }}
                                </span>
                            @endif
                        </td>

                        <td>
                            <p class="table-main-text">
                                {{ $log->sent_at ? \Carbon\Carbon::parse($log->sent_at)->format('d M Y') : '-' }}
                            </p>

                            <p class="table-sub-text">
                                {{ $log->sent_at ? \Carbon\Carbon::parse($log->sent_at)->format('h:i A') : 'Not sent yet' }}
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
    initAdminDataTable('.datatable-WhatsappLogs', {
        searchPlaceholder: 'Search WhatsApp logs...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ WhatsApp logs'
    });
});
</script>
@endsection