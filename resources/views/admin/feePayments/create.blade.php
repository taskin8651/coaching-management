@extends('layouts.admin')

@section('page-title', 'Add Fee Payment')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.fee-payments.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Add Fee Payment</h2>

        <p class="admin-page-subtitle">
            Create student fee receipt and payment record
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.fee-payments.store') }}">
    @csrf

    <div class="admin-form-grid">

        {{-- STUDENT MAPPING --}}
        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>

                <div>
                    <p class="form-card-title">Student Mapping</p>
                    <p class="form-card-subtitle">Select student, fee structure, branch, course and batch</p>
                </div>
            </div>

            <div class="form-card-body">

                {{-- STUDENT --}}
                <div class="field-group">
                    <label class="field-label" for="student_id">
                        Student <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-user-graduate icon"></i>

                        <select name="student_id"
                                id="student_id"
                                required
                                class="field-input {{ $errors->has('student_id') ? 'error' : '' }}">
                            @foreach($students as $id => $student)
                                <option value="{{ $id }}" {{ old('student_id') == $id ? 'selected' : '' }}>
                                    {{ $student }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('student_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('student_id') }}
                        </p>
                    @endif
                </div>

                {{-- FEE STRUCTURE --}}
                <div class="field-group">
                    <label class="field-label" for="fee_structure_id">
                        Fee Structure
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-list-alt icon"></i>

                        <select name="fee_structure_id"
                                id="fee_structure_id"
                                class="field-input {{ $errors->has('fee_structure_id') ? 'error' : '' }}">
                            @foreach($feeStructures as $id => $feeStructure)
                                <option value="{{ $id }}" {{ old('fee_structure_id') == $id ? 'selected' : '' }}>
                                    {{ $feeStructure }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('fee_structure_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('fee_structure_id') }}
                        </p>
                    @else
                        <p class="field-hint">Select fee structure to auto-fill total fee.</p>
                    @endif
                </div>

                {{-- BRANCH --}}
                <div class="field-group">
                    <label class="field-label" for="branch_id">
                        Branch
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-school icon"></i>

                        <select name="branch_id"
                                id="branch_id"
                                class="field-input {{ $errors->has('branch_id') ? 'error' : '' }}">
                            @foreach($branches as $id => $branch)
                                <option value="{{ $id }}" {{ old('branch_id') == $id ? 'selected' : '' }}>
                                    {{ $branch }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('branch_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('branch_id') }}
                        </p>
                    @endif
                </div>

                {{-- COURSE --}}
                <div class="field-group">
                    <label class="field-label" for="course_id">
                        Course
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-book icon"></i>

                        <select name="course_id"
                                id="course_id"
                                class="field-input {{ $errors->has('course_id') ? 'error' : '' }}">
                            @foreach($courses as $id => $course)
                                <option value="{{ $id }}" {{ old('course_id') == $id ? 'selected' : '' }}>
                                    {{ $course }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('course_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('course_id') }}
                        </p>
                    @endif
                </div>

                {{-- BATCH --}}
                <div class="field-group">
                    <label class="field-label" for="batch_id">
                        Batch
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-users icon"></i>

                        <select name="batch_id"
                                id="batch_id"
                                class="field-input {{ $errors->has('batch_id') ? 'error' : '' }}">
                            @foreach($batches as $id => $batch)
                                <option value="{{ $id }}" {{ old('batch_id') == $id ? 'selected' : '' }}>
                                    {{ $batch }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('batch_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('batch_id') }}
                        </p>
                    @endif
                </div>

            </div>
        </div>

        {{-- PAYMENT DETAILS --}}
        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-rupee-sign"></i>
                </div>

                <div>
                    <p class="form-card-title">Payment Details</p>
                    <p class="form-card-subtitle">Amount calculation and payment mode</p>
                </div>
            </div>

            <div class="form-card-body">

                {{-- RECEIPT NO --}}
                <div class="field-group">
                    <label class="field-label" for="receipt_no">
                        Receipt No
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-receipt icon"></i>

                        <input type="text"
                               name="receipt_no"
                               id="receipt_no"
                               value="{{ old('receipt_no') }}"
                               placeholder="Auto generated if blank"
                               class="field-input {{ $errors->has('receipt_no') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('receipt_no'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('receipt_no') }}
                        </p>
                    @else
                        <p class="field-hint">Leave blank for auto receipt number.</p>
                    @endif
                </div>

                {{-- TOTAL FEE --}}
                <div class="field-group">
                    <label class="field-label" for="total_fee">
                        Total Fee <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-rupee-sign icon"></i>

                        <input type="number"
                               step="0.01"
                               min="0"
                               name="total_fee"
                               id="total_fee"
                               value="{{ old('total_fee', 0) }}"
                               required
                               class="field-input {{ $errors->has('total_fee') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('total_fee'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('total_fee') }}
                        </p>
                    @endif
                </div>

                {{-- DISCOUNT --}}
                <div class="field-group">
                    <label class="field-label" for="discount">
                        Discount
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-tags icon"></i>

                        <input type="number"
                               step="0.01"
                               min="0"
                               name="discount"
                               id="discount"
                               value="{{ old('discount', 0) }}"
                               class="field-input {{ $errors->has('discount') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('discount'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('discount') }}
                        </p>
                    @endif
                </div>

                {{-- PAID AMOUNT --}}
                <div class="field-group">
                    <label class="field-label" for="paid_amount">
                        Paid Amount <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-money-bill-wave icon"></i>

                        <input type="number"
                               step="0.01"
                               min="0"
                               name="paid_amount"
                               id="paid_amount"
                               value="{{ old('paid_amount', 0) }}"
                               required
                               class="field-input {{ $errors->has('paid_amount') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('paid_amount'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('paid_amount') }}
                        </p>
                    @endif
                </div>

                {{-- PAYMENT MODE --}}
                <div class="field-group">
                    <label class="field-label" for="payment_mode">
                        Payment Mode <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-credit-card icon"></i>

                        <select name="payment_mode"
                                id="payment_mode"
                                required
                                class="field-input {{ $errors->has('payment_mode') ? 'error' : '' }}">
                            @foreach($paymentModes as $key => $mode)
                                <option value="{{ $key }}" {{ old('payment_mode', 'cash') == $key ? 'selected' : '' }}>
                                    {{ $mode }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('payment_mode'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('payment_mode') }}
                        </p>
                    @endif
                </div>

                {{-- PAYMENT DATE --}}
                <div class="field-group">
                    <label class="field-label" for="payment_date">
                        Payment Date
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar icon"></i>

                        <input type="date"
                               name="payment_date"
                               id="payment_date"
                               value="{{ old('payment_date', date('Y-m-d')) }}"
                               class="field-input {{ $errors->has('payment_date') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('payment_date'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('payment_date') }}
                        </p>
                    @endif
                </div>

                {{-- COLLECTED BY --}}
                <div class="field-group">
                    <label class="field-label" for="collected_by_id">
                        Collected By
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-user-tie icon"></i>

                        <select name="collected_by_id"
                                id="collected_by_id"
                                class="field-input {{ $errors->has('collected_by_id') ? 'error' : '' }}">
                            @foreach($users as $id => $user)
                                <option value="{{ $id }}" {{ old('collected_by_id', auth()->id()) == $id ? 'selected' : '' }}>
                                    {{ $user }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('collected_by_id'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('collected_by_id') }}
                        </p>
                    @endif
                </div>

            </div>
        </div>

        {{-- LIVE CALCULATION --}}
        <div class="form-card" style="grid-column: 1 / -1;">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-calculator"></i>
                </div>

                <div>
                    <p class="form-card-title">Live Calculation</p>
                    <p class="form-card-subtitle">This is only preview. Final calculation will be done by backend.</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="stats-grid">
                    <div class="stat-card">
                        <p class="stat-label">Payable Amount</p>
                        <p class="stat-value" id="payablePreview">₹0</p>
                    </div>

                    <div class="stat-card">
                        <p class="stat-label">Due Amount</p>
                        <p class="stat-value" id="duePreview">₹0</p>
                    </div>
                </div>

                {{-- REMARKS --}}
                <div class="field-group">
                    <label class="field-label" for="remarks">
                        Remarks
                    </label>

                    <textarea name="remarks"
                              id="remarks"
                              rows="4"
                              placeholder="Enter payment remarks"
                              class="field-input {{ $errors->has('remarks') ? 'error' : '' }}">{{ old('remarks') }}</textarea>

                    @if($errors->has('remarks'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('remarks') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">
            <i class="fas fa-check"></i>
            {{ trans('global.save') }}
        </button>

        <a href="{{ route('admin.fee-payments.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</form>

@endsection

@section('scripts')
@parent

<script>
const feeStructures = @json($feeStructureData);

function updateFeePreview() {
    const totalInput = document.getElementById('total_fee');
    const discountInput = document.getElementById('discount');
    const paidInput = document.getElementById('paid_amount');

    const total = parseFloat(totalInput ? totalInput.value || 0 : 0);
    const discount = parseFloat(discountInput ? discountInput.value || 0 : 0);
    const paid = parseFloat(paidInput ? paidInput.value || 0 : 0);

    const payable = Math.max(total - discount, 0);
    const due = Math.max(payable - paid, 0);

    const payablePreview = document.getElementById('payablePreview');
    const duePreview = document.getElementById('duePreview');

    if (payablePreview) {
        payablePreview.innerText = '₹' + payable.toFixed(2);
    }

    if (duePreview) {
        duePreview.innerText = '₹' + due.toFixed(2);
    }
}

function applyFeeStructure() {
    const feeStructureSelect = document.getElementById('fee_structure_id');

    if (!feeStructureSelect) {
        return;
    }

    const id = feeStructureSelect.value;

    if (!id || !feeStructures[id]) {
        return;
    }

    const data = feeStructures[id];

    const branch = document.getElementById('branch_id');
    const course = document.getElementById('course_id');
    const batch = document.getElementById('batch_id');
    const total = document.getElementById('total_fee');

    if (branch && data.branch_id) {
        branch.value = data.branch_id;
    }

    if (course && data.course_id) {
        course.value = data.course_id;
    }

    if (batch && data.batch_id) {
        batch.value = data.batch_id;
    }

    if (total && data.total_fee) {
        total.value = parseFloat(data.total_fee).toFixed(2);
    }

    updateFeePreview();
}

document.addEventListener('DOMContentLoaded', function () {
    ['total_fee', 'discount', 'paid_amount'].forEach(function (id) {
        const el = document.getElementById(id);

        if (el) {
            el.addEventListener('input', updateFeePreview);
        }
    });

    const feeStructureSelect = document.getElementById('fee_structure_id');

    if (feeStructureSelect) {
        feeStructureSelect.addEventListener('change', applyFeeStructure);
    }

    updateFeePreview();
});
</script>

@endsection