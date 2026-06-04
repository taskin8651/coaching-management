@extends('layouts.admin')

@section('page-title', 'Add WhatsApp Setting')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.whatsapp-settings.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Add WhatsApp Setting</h2>

        <p class="admin-page-subtitle">
            Configure WhatsApp API, sender number and biometric device token
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.whatsapp-settings.store') }}">
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fab fa-whatsapp"></i>
                </div>

                <div>
                    <p class="form-card-title">WhatsApp API Details</p>
                    <p class="form-card-subtitle">Provider, API URL and authentication token</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="api_provider">
                        Provider
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-plug icon"></i>

                        <input type="text"
                               name="api_provider"
                               id="api_provider"
                               value="{{ old('api_provider') }}"
                               placeholder="Example: Gupshup, Interakt, WATI, Custom API"
                               class="field-input {{ $errors->has('api_provider') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('api_provider'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('api_provider') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="api_base_url">
                        API Base URL
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-link icon"></i>

                        <input type="url"
                               name="api_base_url"
                               id="api_base_url"
                               value="{{ old('api_base_url') }}"
                               placeholder="https://api.example.com"
                               class="field-input {{ $errors->has('api_base_url') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('api_base_url'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('api_base_url') }}
                        </p>
                    @else
                        <p class="field-hint">
                            <i class="fas fa-info-circle"></i>
                            API request isi base URL par send hoga.
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="api_key">
                        API Key / Token
                    </label>

                    <div class="input-icon-wrap textarea-wrap">
                        <i class="fas fa-key icon"></i>

                        <textarea name="api_key"
                                  id="api_key"
                                  rows="4"
                                  placeholder="Enter API key, bearer token or secret key"
                                  class="field-input {{ $errors->has('api_key') ? 'error' : '' }}">{{ old('api_key') }}</textarea>
                    </div>

                    @if($errors->has('api_key'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('api_key') }}
                        </p>
                    @else
                        <p class="field-hint">
                            <i class="fas fa-shield-alt"></i>
                            Token ko secure rakhein. Ye WhatsApp message sending ke liye use hoga.
                        </p>
                    @endif
                </div>

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>

                <div>
                    <p class="form-card-title">Sender & Device</p>
                    <p class="form-card-subtitle">Sender number, biometric token and status</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="sender_number">
                        Sender Number
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fab fa-whatsapp icon"></i>

                        <input type="text"
                               name="sender_number"
                               id="sender_number"
                               value="{{ old('sender_number') }}"
                               placeholder="Example: 919876543210"
                               class="field-input {{ $errors->has('sender_number') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('sender_number'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('sender_number') }}
                        </p>
                    @else
                        <p class="field-hint">
                            <i class="fas fa-info-circle"></i>
                            Country code ke sath sender number add karein.
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="biometric_device_token">
                        Biometric Device Token
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-fingerprint icon"></i>

                        <input type="text"
                               name="biometric_device_token"
                               id="biometric_device_token"
                               value="{{ old('biometric_device_token') }}"
                               placeholder="Enter biometric device token"
                               class="field-input {{ $errors->has('biometric_device_token') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('biometric_device_token'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('biometric_device_token') }}
                        </p>
                    @else
                        <p class="field-hint">
                            <i class="fas fa-fingerprint"></i>
                            Biometric attendance sync ke liye token use hoga.
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="status">
                        Status
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-toggle-on icon"></i>

                        <select name="status"
                                id="status"
                                class="field-input {{ $errors->has('status') ? 'error' : '' }}">
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>
                                Active
                            </option>

                            <option value="inactive" {{ old('status', 'inactive') === 'inactive' ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>
                    </div>

                    @if($errors->has('status'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('status') }}
                        </p>
                    @else
                        <p class="field-hint">
                            <i class="fas fa-info-circle"></i>
                            Active setting WhatsApp notification sending me use hogi.
                        </p>
                    @endif
                </div>

                <div class="form-info-box">
                    <p>
                        <i class="fas fa-bell"></i>
                        Student attendance, fee reminder, homework aur important updates guardian ko WhatsApp par bheje ja sakte hain.
                    </p>
                </div>

            </div>
        </div>

        <div class="form-card" style="grid-column:1/-1;">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-eye"></i>
                </div>

                <div>
                    <p class="form-card-title">Setting Preview</p>
                    <p class="form-card-subtitle">Review provider, sender and current status</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="stats-grid" style="margin-bottom:0;">
                    <div class="stat-card">
                        <p class="stat-label">Provider</p>
                        <p class="stat-value" id="previewProvider" style="font-size:22px;">-</p>
                    </div>

                    <div class="stat-card">
                        <p class="stat-label">Sender</p>
                        <p class="stat-value" id="previewSender" style="font-size:22px;">-</p>
                    </div>

                    <div class="stat-card">
                        <p class="stat-label">Biometric Token</p>
                        <p class="stat-value" id="previewDevice" style="font-size:22px;">-</p>
                    </div>

                    <div class="stat-card">
                        <p class="stat-label">Status</p>
                        <p class="stat-value" id="previewStatus" style="font-size:22px;">Inactive</p>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">
            <i class="fas fa-check"></i>
            {{ trans('global.save') }}
        </button>

        <a href="{{ route('admin.whatsapp-settings.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</form>

@endsection

@section('scripts')
@parent
<script>
function formatText(value) {
    if (!value) return '-';

    return value
        .replace(/_/g, ' ')
        .replace(/\b\w/g, function (char) {
            return char.toUpperCase();
        });
}

function maskValue(value) {
    if (!value) return '-';

    if (value.length <= 6) {
        return value;
    }

    return value.substring(0, 3) + '***' + value.substring(value.length - 3);
}

function updateWhatsAppPreview() {
    const provider = document.getElementById('api_provider');
    const sender = document.getElementById('sender_number');
    const device = document.getElementById('biometric_device_token');
    const status = document.getElementById('status');

    document.getElementById('previewProvider').innerText = provider && provider.value ? provider.value : '-';
    document.getElementById('previewSender').innerText = sender && sender.value ? sender.value : '-';
    document.getElementById('previewDevice').innerText = maskValue(device && device.value ? device.value : '');
    document.getElementById('previewStatus').innerText = formatText(status ? status.value : 'inactive');
}

document.addEventListener('DOMContentLoaded', function () {
    ['api_provider', 'sender_number', 'biometric_device_token', 'status'].forEach(function (id) {
        const el = document.getElementById(id);

        if (el) {
            el.addEventListener('input', updateWhatsAppPreview);
            el.addEventListener('change', updateWhatsAppPreview);
        }
    });

    updateWhatsAppPreview();
});
</script>
@endsection