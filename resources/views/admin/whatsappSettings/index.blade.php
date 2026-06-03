@extends('layouts.admin')

@section('page-title', 'WhatsApp Settings')

@section('content')
<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">WhatsApp Settings</h2>
        <p class="admin-page-subtitle">API provider configuration for guardian alerts</p>
    </div>
    @can('whatsapp_settings_create')
        <a href="{{ route('admin.whatsapp-settings.create') }}" class="btn-primary"><i class="fas fa-plus"></i> Add Setting</a>
    @endcan
</div>

<div class="page-card">
    <div class="page-card-table">
        <table class="min-w-full datatable datatable-WhatsappSettings">
            <thead><tr><th>ID</th><th>Provider</th><th>Base URL</th><th>Sender</th><th>Status</th><th style="text-align:right;">Actions</th></tr></thead>
            <tbody>
                @foreach($settings as $setting)
                    <tr>
                        <td>#{{ $setting->id }}</td>
                        <td>{{ $setting->api_provider ?? '-' }}</td>
                        <td>{{ $setting->api_base_url ?? '-' }}</td>
                        <td>{{ $setting->sender_number ?? '-' }}</td>
                        <td><span class="status-pill {{ $setting->status === 'active' ? 'success' : 'warning' }}">{{ ucfirst($setting->status) }}</span></td>
                        <td style="text-align:right;">
                            @can('whatsapp_settings_edit')
                                <a href="{{ route('admin.whatsapp-settings.edit', $setting->id) }}" class="btn-outline btn-outline-edit"><i class="fas fa-pencil-alt"></i> Edit</a>
                            @endcan
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
<script>$(function(){ initAdminDataTable('.datatable-WhatsappSettings', { searchPlaceholder: 'Search settings...' }); });</script>
@endsection
