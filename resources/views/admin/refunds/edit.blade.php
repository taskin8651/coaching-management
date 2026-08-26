@extends('layouts.admin')

@section('page-title', 'Edit Refund')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.refunds.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">Edit Refund</h2>
        <p class="admin-page-subtitle">{{ $refund->student->user->name ?? '-' }}</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.refunds.update', $refund->id) }}">
    @csrf
    @method('PUT')

    <div class="admin-form-grid">
        <div class="form-card" style="grid-column: 1 / -1;">
            <div class="form-card-header">
                <div class="form-card-icon"><i class="fas fa-undo-alt"></i></div>
                <div>
                    <p class="form-card-title">Refund Details</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="field-group">
                    <label class="field-label">Fee Account <span class="req">*</span></label>
                    <select name="fee_account_id" required class="field-input {{ $errors->has('fee_account_id') ? 'error' : '' }}">
                        @foreach($feeAccounts as $id => $account)
                            <option value="{{ $id }}" {{ old('fee_account_id', $refund->fee_account_id) == $id ? 'selected' : '' }}>{{ $account }}</option>
                        @endforeach
                    </select>
                    @if($errors->has('fee_account_id')) <p class="field-error">{{ $errors->first('fee_account_id') }}</p> @endif
                </div>

                <div class="field-group">
                    <label class="field-label">Amount <span class="req">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $refund->amount) }}" required class="field-input {{ $errors->has('amount') ? 'error' : '' }}">
                    @if($errors->has('amount')) <p class="field-error">{{ $errors->first('amount') }}</p> @endif
                </div>

                <div class="field-group">
                    <label class="field-label">Mode <span class="req">*</span></label>
                    <select name="mode" required class="field-input">
                        @foreach(['cash' => 'Cash', 'upi' => 'UPI', 'bank_transfer' => 'Bank Transfer', 'cheque' => 'Cheque', 'card' => 'Card', 'other' => 'Other'] as $key => $label)
                            <option value="{{ $key }}" {{ old('mode', $refund->mode) == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label">Reference No</label>
                    <input type="text" name="reference_no" value="{{ old('reference_no', $refund->reference_no) }}" class="field-input">
                </div>

                <div class="field-group">
                    <label class="field-label">Refund Date <span class="req">*</span></label>
                    <input type="date" name="refund_date" value="{{ old('refund_date', optional($refund->refund_date)->format('Y-m-d')) }}" required class="field-input {{ $errors->has('refund_date') ? 'error' : '' }}">
                </div>

                <div class="field-group" style="grid-column: 1 / -1;">
                    <label class="field-label">Reason <span class="req">*</span></label>
                    <textarea name="reason" rows="3" required class="field-input {{ $errors->has('reason') ? 'error' : '' }}">{{ old('reason', $refund->reason) }}</textarea>
                    @if($errors->has('reason')) <p class="field-error">{{ $errors->first('reason') }}</p> @endif
                </div>

                <div class="field-group" style="grid-column: 1 / -1;">
                    <label class="field-label">Remarks</label>
                    <textarea name="remarks" rows="2" class="field-input">{{ old('remarks', $refund->remarks) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary"><i class="fas fa-check"></i> {{ trans('global.save') }}</button>
        <a href="{{ route('admin.refunds.index') }}" class="btn-ghost">{{ trans('global.cancel') }}</a>
    </div>
</form>

@endsection
