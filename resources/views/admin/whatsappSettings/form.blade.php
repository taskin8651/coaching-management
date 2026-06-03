<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.whatsapp-settings.index') }}" class="admin-back-link">{{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">{{ $setting ? 'Edit WhatsApp Setting' : 'Add WhatsApp Setting' }}</h2>
    </div>
</div>

<form method="POST" action="{{ $action }}">
    @csrf
    @if($method !== 'POST') @method($method) @endif
    <div class="admin-form-grid">
        <div class="form-card" style="grid-column:1/-1;">
            <div class="form-card-body">
                <div class="field-group"><label class="field-label">Provider</label><input class="field-input" name="api_provider" value="{{ old('api_provider', $setting->api_provider ?? '') }}"></div>
                <div class="field-group"><label class="field-label">API Base URL</label><input class="field-input" name="api_base_url" value="{{ old('api_base_url', $setting->api_base_url ?? '') }}"></div>
                <div class="field-group"><label class="field-label">API Key / Token</label><textarea class="field-input" name="api_key" rows="3">{{ old('api_key', $setting->api_key ?? '') }}</textarea></div>
                <div class="field-group"><label class="field-label">Sender Number</label><input class="field-input" name="sender_number" value="{{ old('sender_number', $setting->sender_number ?? '') }}"></div>
                <div class="field-group"><label class="field-label">Biometric Device Token</label><input class="field-input" name="biometric_device_token" value="{{ old('biometric_device_token', $setting->biometric_device_token ?? '') }}"></div>
                <div class="field-group"><label class="field-label">Status</label><select class="field-input" name="status"><option value="active" {{ old('status', $setting->status ?? '') === 'active' ? 'selected' : '' }}>Active</option><option value="inactive" {{ old('status', $setting->status ?? 'inactive') === 'inactive' ? 'selected' : '' }}>Inactive</option></select></div>
            </div>
        </div>
    </div>
    <div class="form-actions"><button class="btn-primary" type="submit"><i class="fas fa-check"></i> {{ trans('global.save') }}</button></div>
</form>
