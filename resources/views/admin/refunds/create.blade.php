@extends('layouts.admin')

@section('page-title', 'Request Refund')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.refunds.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">Request Refund</h2>
        <p class="admin-page-subtitle">Against a specific payment, or from the student's advance/credit balance</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.refunds.store') }}">
    @csrf

    <div class="admin-form-grid">
        <div class="form-card" style="grid-column: 1 / -1;">
            <div class="form-card-header">
                <div class="form-card-icon"><i class="fas fa-undo-alt"></i></div>
                <div>
                    <p class="form-card-title">Refund Details</p>
                    <p class="form-card-subtitle">Approval required before completion — money only moves once marked Completed</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="field-group">
                    <label class="field-label">Student <span class="req">*</span></label>
                    <select name="student_id" id="student_id" required class="field-input {{ $errors->has('student_id') ? 'error' : '' }}">
                        @foreach($students as $id => $student)
                            <option value="{{ $id }}" {{ old('student_id') == $id ? 'selected' : '' }}>{{ $student }}</option>
                        @endforeach
                    </select>
                    @if($errors->has('student_id')) <p class="field-error">{{ $errors->first('student_id') }}</p> @endif
                </div>

                <div class="field-group">
                    <label class="field-label">Originating Payment</label>
                    <select name="fee_payment_id" id="fee_payment_id" class="field-input {{ $errors->has('fee_payment_id') ? 'error' : '' }}">
                        <option value="">None — refund from advance/credit balance</option>
                    </select>
                    @if($errors->has('fee_payment_id'))
                        <p class="field-error">{{ $errors->first('fee_payment_id') }}</p>
                    @else
                        <p class="field-hint">Leave as "None" to refund purely from the student's advance/credit balance instead.</p>
                    @endif
                </div>

                <div class="field-group" id="installmentGroup" style="display:none;">
                    <label class="field-label">Installment</label>
                    <select name="fee_installment_id" id="fee_installment_id" class="field-input"></select>
                    <p class="field-hint">This payment was split across multiple installments — choose which one this refund reverses.</p>
                </div>

                <div class="field-group">
                    <label class="field-label">Fee Account <span class="req">*</span></label>
                    <select name="fee_account_id" required class="field-input {{ $errors->has('fee_account_id') ? 'error' : '' }}">
                        @foreach($feeAccounts as $id => $account)
                            <option value="{{ $id }}" {{ old('fee_account_id') == $id ? 'selected' : '' }}>{{ $account }}</option>
                        @endforeach
                    </select>
                    <p class="field-hint">Account the money is being paid back out from.</p>
                    @if($errors->has('fee_account_id')) <p class="field-error">{{ $errors->first('fee_account_id') }}</p> @endif
                </div>

                <div class="field-group">
                    <label class="field-label">Amount <span class="req">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount" id="amount" value="{{ old('amount') }}" required class="field-input {{ $errors->has('amount') ? 'error' : '' }}">
                    <p class="field-hint" id="refundableHint"></p>
                    @if($errors->has('amount')) <p class="field-error">{{ $errors->first('amount') }}</p> @endif
                </div>

                <div class="field-group">
                    <label class="field-label">Mode <span class="req">*</span></label>
                    <select name="mode" required class="field-input">
                        <option value="cash" {{ old('mode', 'cash') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="upi" {{ old('mode') == 'upi' ? 'selected' : '' }}>UPI</option>
                        <option value="bank_transfer" {{ old('mode') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="cheque" {{ old('mode') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                        <option value="card" {{ old('mode') == 'card' ? 'selected' : '' }}>Card</option>
                        <option value="other" {{ old('mode') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="field-group">
                    <label class="field-label">Reference No</label>
                    <input type="text" name="reference_no" value="{{ old('reference_no') }}" class="field-input">
                </div>

                <div class="field-group">
                    <label class="field-label">Refund Date <span class="req">*</span></label>
                    <input type="date" name="refund_date" value="{{ old('refund_date', now()->format('Y-m-d')) }}" required class="field-input {{ $errors->has('refund_date') ? 'error' : '' }}">
                </div>

                <div class="field-group" style="grid-column: 1 / -1;">
                    <label class="field-label">Reason <span class="req">*</span></label>
                    <textarea name="reason" rows="3" required class="field-input {{ $errors->has('reason') ? 'error' : '' }}">{{ old('reason') }}</textarea>
                    @if($errors->has('reason')) <p class="field-error">{{ $errors->first('reason') }}</p> @endif
                </div>

                <div class="field-group" style="grid-column: 1 / -1;">
                    <label class="field-label">Remarks</label>
                    <textarea name="remarks" rows="2" class="field-input">{{ old('remarks') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Submit Refund Request</button>
        <a href="{{ route('admin.refunds.index') }}" class="btn-ghost">{{ trans('global.cancel') }}</a>
    </div>
</form>

<script>
const paymentsByStudent = @json($paymentsByStudent);

function fillSelect(select, items, placeholder, valueKey, labelKey) {
    select.innerHTML = '';
    select.appendChild(new Option(placeholder, ''));
    items.forEach(item => select.appendChild(new Option(item[labelKey], item[valueKey])));
}

function onStudentChange() {
    const studentId = document.getElementById('student_id').value;
    const payments = paymentsByStudent[studentId] || [];
    const paymentSelect = document.getElementById('fee_payment_id');

    fillSelect(paymentSelect, payments, 'None — refund from advance/credit balance', 'id', 'name');
    onPaymentChange();
}

function onPaymentChange() {
    const studentId = document.getElementById('student_id').value;
    const paymentId = document.getElementById('fee_payment_id').value;
    const payments = paymentsByStudent[studentId] || [];
    const payment = payments.find(p => String(p.id) === String(paymentId));
    const installmentGroup = document.getElementById('installmentGroup');
    const installmentSelect = document.getElementById('fee_installment_id');
    const hint = document.getElementById('refundableHint');

    if (payment && payment.installments && payment.installments.length > 1) {
        installmentGroup.style.display = '';
        fillSelect(installmentSelect, payment.installments, 'Select installment', 'id', 'name');
    } else {
        installmentGroup.style.display = 'none';
        installmentSelect.innerHTML = '';
    }

    hint.innerText = payment ? ('Refundable on this payment: ₹' + payment.refundable.toFixed(2)) : '';
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('student_id').addEventListener('change', onStudentChange);
    document.getElementById('fee_payment_id').addEventListener('change', onPaymentChange);
    onStudentChange();
});
</script>

@endsection
