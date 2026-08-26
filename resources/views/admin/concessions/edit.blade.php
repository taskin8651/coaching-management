@extends('layouts.admin')

@section('page-title', 'Edit Concession')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.concessions.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">Edit Concession</h2>
        <p class="admin-page-subtitle">For: {{ $concession->student->user->name ?? '-' }}</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.concessions.update', $concession->id) }}">
    @csrf
    @method('PUT')

    <div class="admin-form-grid">
        <div class="form-card" style="grid-column: 1 / -1;">
            <div class="form-card-header">
                <div class="form-card-icon"><i class="fas fa-percent"></i></div>
                <div>
                    <p class="form-card-title">Concession Details</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="field-group">
                    <label class="field-label">Type <span class="req">*</span></label>
                    <input type="text" name="type" value="{{ old('type', $concession->type) }}" required class="field-input {{ $errors->has('type') ? 'error' : '' }}">
                    @if($errors->has('type')) <p class="field-error">{{ $errors->first('type') }}</p> @endif
                </div>

                <div class="field-group">
                    <label class="field-label">Value Type <span class="req">*</span></label>
                    <select name="amount_type" id="amount_type" required class="field-input">
                        <option value="fixed" {{ old('amount_type', $concession->amount_type) == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                        <option value="percentage" {{ old('amount_type', $concession->amount_type) == 'percentage' ? 'selected' : '' }}>Percentage</option>
                    </select>
                </div>

                <div class="field-group" id="amountGroup">
                    <label class="field-label">Amount (₹)</label>
                    <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $concession->amount) }}" class="field-input {{ $errors->has('amount') ? 'error' : '' }}">
                    @if($errors->has('amount')) <p class="field-error">{{ $errors->first('amount') }}</p> @endif
                </div>

                <div class="field-group" id="percentageGroup" style="display:none;">
                    <label class="field-label">Percentage (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="percentage" value="{{ old('percentage', $concession->percentage) }}" class="field-input {{ $errors->has('percentage') ? 'error' : '' }}">
                    @if($errors->has('percentage')) <p class="field-error">{{ $errors->first('percentage') }}</p> @endif
                </div>

                <div class="field-group" style="grid-column: 1 / -1;">
                    <label class="field-label">Reason</label>
                    <textarea name="reason" rows="3" class="field-input">{{ old('reason', $concession->reason) }}</textarea>
                </div>

                <div class="field-group" style="grid-column: 1 / -1;">
                    <label class="field-label">Remarks</label>
                    <textarea name="remarks" rows="2" class="field-input">{{ old('remarks', $concession->remarks) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary"><i class="fas fa-check"></i> {{ trans('global.save') }}</button>
        <a href="{{ route('admin.concessions.index') }}" class="btn-ghost">{{ trans('global.cancel') }}</a>
    </div>
</form>

<script>
function toggleConcessionValueFields() {
    const isPercentage = document.getElementById('amount_type').value === 'percentage';
    document.getElementById('amountGroup').style.display = isPercentage ? 'none' : '';
    document.getElementById('percentageGroup').style.display = isPercentage ? '' : 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    toggleConcessionValueFields();
    document.getElementById('amount_type').addEventListener('change', toggleConcessionValueFields);
});
</script>

@endsection
