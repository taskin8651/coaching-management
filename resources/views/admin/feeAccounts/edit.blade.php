@extends('layouts.admin')

@section('page-title', 'Edit Fee Account')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.fee-accounts.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">Edit Fee Account</h2>
        <p class="admin-page-subtitle">Update collection account details</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.fee-accounts.update', $feeAccount->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon"><i class="fas fa-university"></i></div>
                <div>
                    <p class="form-card-title">Account Information</p>
                    <p class="form-card-subtitle">Basic account identity</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="field-group">
                    <label class="field-label">Account Name <span class="req">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $feeAccount->name) }}" required class="field-input {{ $errors->has('name') ? 'error' : '' }}">
                    @if($errors->has('name')) <p class="field-error">{{ $errors->first('name') }}</p> @endif
                </div>

                <div class="field-group">
                    <label class="field-label">Account Code <span class="req">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $feeAccount->code) }}" required class="field-input {{ $errors->has('code') ? 'error' : '' }}">
                    @if($errors->has('code')) <p class="field-error">{{ $errors->first('code') }}</p> @endif
                </div>

                <div class="field-group">
                    <label class="field-label">Branch</label>
                    <select name="branch_id" class="field-input">
                        @foreach($branches as $id => $branch)
                            <option value="{{ $id }}" {{ old('branch_id', $feeAccount->branch_id) == $id ? 'selected' : '' }}>{{ $branch }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label">Account Type <span class="req">*</span></label>
                    <select name="type" id="type" required class="field-input">
                        <option value="cash" {{ old('type', $feeAccount->type) == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="bank" {{ old('type', $feeAccount->type) == 'bank' ? 'selected' : '' }}>Bank</option>
                        <option value="other" {{ old('type', $feeAccount->type) == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label">Status <span class="req">*</span></label>
                    <select name="status" class="field-input">
                        <option value="active" {{ old('status', $feeAccount->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $feeAccount->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-card" id="bankFieldsCard">
            <div class="form-card-header">
                <div class="form-card-icon"><i class="fas fa-money-check-alt"></i></div>
                <div>
                    <p class="form-card-title">Banking Details</p>
                    <p class="form-card-subtitle">Shown when Account Type = Bank</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="field-group">
                    <label class="field-label">Bank Name</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $feeAccount->bank_name) }}" class="field-input {{ $errors->has('bank_name') ? 'error' : '' }}">
                    @if($errors->has('bank_name')) <p class="field-error">{{ $errors->first('bank_name') }}</p> @endif
                </div>

                <div class="field-group">
                    <label class="field-label">Account Number</label>
                    <input type="text" name="account_number" value="{{ old('account_number', $feeAccount->account_number) }}" class="field-input {{ $errors->has('account_number') ? 'error' : '' }}">
                    @if($errors->has('account_number')) <p class="field-error">{{ $errors->first('account_number') }}</p> @endif
                </div>

                <div class="field-group">
                    <label class="field-label">IFSC Code</label>
                    <input type="text" name="ifsc_code" value="{{ old('ifsc_code', $feeAccount->ifsc_code) }}" class="field-input">
                </div>

                <div class="field-group">
                    <label class="field-label">UPI ID</label>
                    <input type="text" name="upi_id" value="{{ old('upi_id', $feeAccount->upi_id) }}" class="field-input">
                </div>

                <div class="field-group">
                    <label class="field-label">QR Code Image</label>
                    @if($feeAccount->qr_code_url)
                        <div class="mb-2"><img src="{{ $feeAccount->qr_code_url }}" alt="QR Code" style="max-width:120px;border-radius:12px;"></div>
                    @endif
                    <input type="file" name="qr_code" accept="image/*" class="field-input">
                    @if($errors->has('qr_code')) <p class="field-error">{{ $errors->first('qr_code') }}</p> @endif
                    <p class="field-hint">Uploading a new image replaces the existing one.</p>
                </div>
            </div>
        </div>

        <div class="form-card" style="grid-column: 1 / -1;">
            <div class="form-card-header">
                <div class="form-card-icon"><i class="fas fa-file-invoice"></i></div>
                <div>
                    <p class="form-card-title">GST & Receipt Details</p>
                    <p class="form-card-subtitle">Information printed on receipts for this account</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="field-group">
                    <label class="field-label">
                        <input type="checkbox" name="gst_applicable" value="1" id="gst_applicable" {{ old('gst_applicable', $feeAccount->gst_applicable) ? 'checked' : '' }}>
                        GST Applicable
                    </label>
                </div>

                <div class="field-group" id="gstNumberGroup">
                    <label class="field-label">GST Number</label>
                    <input type="text" name="gst_number" value="{{ old('gst_number', $feeAccount->gst_number) }}" class="field-input {{ $errors->has('gst_number') ? 'error' : '' }}">
                    @if($errors->has('gst_number')) <p class="field-error">{{ $errors->first('gst_number') }}</p> @endif
                </div>

                <div class="field-group" style="grid-column: 1 / -1;">
                    <label class="field-label">Receipt Address / Details</label>
                    <textarea name="receipt_address" rows="3" class="field-input">{{ old('receipt_address', $feeAccount->receipt_address) }}</textarea>
                </div>
            </div>
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary"><i class="fas fa-check"></i> {{ trans('global.save') }}</button>
        <a href="{{ route('admin.fee-accounts.index') }}" class="btn-ghost">{{ trans('global.cancel') }}</a>
    </div>
</form>

<script>
function toggleFeeAccountFields() {
    const type = document.getElementById('type').value;
    document.getElementById('bankFieldsCard').style.display = type === 'bank' ? '' : 'none';

    const gstOn = document.getElementById('gst_applicable').checked;
    document.getElementById('gstNumberGroup').style.display = gstOn ? '' : 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    toggleFeeAccountFields();
    document.getElementById('type').addEventListener('change', toggleFeeAccountFields);
    document.getElementById('gst_applicable').addEventListener('change', toggleFeeAccountFields);
});
</script>

@endsection
