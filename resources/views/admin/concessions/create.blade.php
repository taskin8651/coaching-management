@extends('layouts.admin')

@section('page-title', 'Add Concession')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.concessions.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">Add Concession</h2>
        <p class="admin-page-subtitle">Request a concession/scholarship for a student — requires approval before it affects the ledger</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.concessions.store') }}">
    @csrf

    <div class="admin-form-grid">
        <div class="form-card" style="grid-column: 1 / -1;">
            <div class="form-card-header">
                <div class="form-card-icon"><i class="fas fa-percent"></i></div>
                <div>
                    <p class="form-card-title">Concession Details</p>
                    <p class="form-card-subtitle">Student must already have an assigned fee structure</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="field-group">
                    <label class="field-label">Student <span class="req">*</span></label>
                    <select name="student_id" required class="field-input {{ $errors->has('student_id') ? 'error' : '' }}">
                        @foreach($students as $id => $student)
                            <option value="{{ $id }}" {{ old('student_id') == $id ? 'selected' : '' }}>{{ $student }}</option>
                        @endforeach
                    </select>
                    @if($errors->has('student_id')) <p class="field-error">{{ $errors->first('student_id') }}</p> @endif
                </div>

                <div class="field-group">
                    <label class="field-label">Type <span class="req">*</span></label>
                    <input type="text" name="type" list="concessionTypes" value="{{ old('type') }}" required placeholder="Example: Scholarship, Sibling Discount" class="field-input {{ $errors->has('type') ? 'error' : '' }}">
                    <datalist id="concessionTypes">
                        <option value="Scholarship">
                        <option value="Sibling Discount">
                        <option value="Staff Ward">
                        <option value="Early Bird">
                        <option value="Special Concession">
                    </datalist>
                    @if($errors->has('type')) <p class="field-error">{{ $errors->first('type') }}</p> @endif
                </div>

                <div class="field-group">
                    <label class="field-label">Value Type <span class="req">*</span></label>
                    <select name="amount_type" id="amount_type" required class="field-input">
                        <option value="fixed" {{ old('amount_type', 'fixed') == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                        <option value="percentage" {{ old('amount_type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                    </select>
                </div>

                <div class="field-group" id="amountGroup">
                    <label class="field-label">Amount (₹)</label>
                    <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount') }}" class="field-input {{ $errors->has('amount') ? 'error' : '' }}">
                    @if($errors->has('amount')) <p class="field-error">{{ $errors->first('amount') }}</p> @endif
                </div>

                <div class="field-group" id="percentageGroup" style="display:none;">
                    <label class="field-label">Percentage (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="percentage" value="{{ old('percentage') }}" class="field-input {{ $errors->has('percentage') ? 'error' : '' }}">
                    @if($errors->has('percentage')) <p class="field-error">{{ $errors->first('percentage') }}</p> @endif
                </div>

                <div class="field-group" style="grid-column: 1 / -1;">
                    <label class="field-label">Reason</label>
                    <textarea name="reason" rows="3" class="field-input">{{ old('reason') }}</textarea>
                </div>

                <div class="field-group" style="grid-column: 1 / -1;">
                    <label class="field-label">Remarks</label>
                    <textarea name="remarks" rows="2" class="field-input">{{ old('remarks') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Submit for Approval</button>
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
