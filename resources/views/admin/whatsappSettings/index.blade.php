@extends('layouts.admin')

@section('page-title', 'WhatsApp Settings')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">WhatsApp Settings</h2>
        <p class="admin-page-subtitle">
            API provider configuration for guardian alerts and student notifications
        </p>
    </div>

    @can('whatsapp_settings_create')
        <a href="{{ route('admin.whatsapp-settings.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Setting
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Settings</p>
        <p class="stat-value">{{ $settings->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Active</p>
        <p class="stat-value">{{ $settings->where('status', 'active')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Inactive</p>
        <p class="stat-value">{{ $settings->where('status', 'inactive')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Providers</p>
        <p class="stat-value">{{ $settings->pluck('api_provider')->filter()->unique()->count() }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All WhatsApp API Settings</p>

        <span class="page-card-note">
            <i class="fab fa-whatsapp"></i>
            Manage provider, sender number and API status
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-WhatsappSettings">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Provider</th>
                    <th>Base URL</th>
                    <th>Sender</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($settings as $setting)
                    <tr data-entry-id="{{ $setting->id }}">
                        <td>
                            <span class="id-text">#{{ $loop->iteration }}</span>
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $provider = $setting->api_provider ?? 'WhatsApp';
                                    $colors = ['#25D366','#0EA5E9','#10B981','#F59E0B','#4F46E5','#8B5CF6','#EC4899','#14B8A6'];
                                    $color = $colors[$loop->index % count($colors)];
                                @endphp

                                <div class="avatar-circle" style="background: {{ $color }};">
                                    <i class="fab fa-whatsapp"></i>
                                </div>

                                <div>
                                    <p class="table-main-text">{{ $provider }}</p>
                                    <p class="table-sub-text">API Provider</p>
                                </div>
                            </div>
                        </td>

                        <td>
                            @if($setting->api_base_url)
                                <p class="table-main-text" style="max-width:360px; word-break:break-all;">
                                    {{ \Illuminate\Support\Str::limit($setting->api_base_url, 70) }}
                                </p>
                                <p class="table-sub-text">Endpoint URL</p>
                            @else
                                <span style="font-size:12px;color:#94A3B8;">—</span>
                            @endif
                        </td>

                        <td>
                            @if($setting->sender_number)
                                <span class="code-pill">
                                    {{ $setting->sender_number }}
                                </span>
                            @else
                                <span style="font-size:12px;color:#94A3B8;">—</span>
                            @endif
                        </td>

                        <td>
                            @if($setting->status === 'active')
                                <span class="status-pill success">Active</span>
                            @elseif($setting->status === 'inactive')
                                <span class="status-pill warning">Inactive</span>
                            @else
                                <span class="status-pill" style="background:#F1F5F9;color:#475569;">
                                    {{ ucfirst($setting->status ?? '-') }}
                                </span>
                            @endif
                        </td>

                        <td>
                            <div class="action-row">
                                @can('whatsapp_settings_show')
                                    <a href="{{ route('admin.whatsapp-settings.show', $setting->id) }}" class="btn-outline">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                @endcan

                                @can('whatsapp_settings_edit')
                                    <a href="{{ route('admin.whatsapp-settings.edit', $setting->id) }}" class="btn-outline btn-outline-edit">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('whatsapp_settings_delete')
                                    <form action="{{ route('admin.whatsapp-settings.destroy', $setting->id) }}"
                                          method="POST"
                                          style="display:inline;"
                                          onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
                                        @csrf
                                        @method('DELETE')

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
    initAdminDataTable('.datatable-WhatsappSettings', {
        searchPlaceholder: 'Search WhatsApp settings...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ WhatsApp settings'
    });
});
</script>
@endsection