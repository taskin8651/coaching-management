@extends('layouts.admin')

@section('page-title', 'Add Fee Installment')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.fee-installments.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Add Fee Installment</h2>

        <p class="admin-page-subtitle">
            Create student fee installment with amount, paid amount and due date
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.fee-installments.store') }}">
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>

                <div>
                    <p class="form-card-title">Student & Fee Structure</p>
                    <p class="form-card-subtitle">Select student and related fee structure</p>
                </div>
            </div>

            <div class="form-card-body">

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
                            <option value="">Select Student</option>

                            @foreach($students as $id => $name)
                                <option value="{{ $id }}" {{ old('student_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
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

                <div class="field-group">
                    <label class="field-label" for="fee_structure_id">
                        Fee Structure
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-file-invoice-dollar icon"></i>

                        <select name="fee_structure_id"
                                id="fee_structure_id"
                                class="field-input {{ $errors->has('fee_structure_id') ? 'error' : '' }}">
                            <option value="">Select Fee Structure</option>

                            @foreach($feeStructures as $id => $name)
                                <option value="{{ $id }}" {{ old('fee_structure_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
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
                        <p class="field-hint">
                            <i class="fas fa-info-circle"></i>
                            Optional, but recommended for proper fee tracking
                        </p>
                    @endif
                </div>

                <div class="form-info-box">
                    <p>
                        <i class="fas fa-lightbulb"></i>
                        Installment will be connected with selected student and fee structure.
                    </p>
                </div>

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-rupee-sign"></i>
                </div>

                <div>
                    <p class="form-card-title">Installment Details</p>
                    <p class="form-card-subtitle">Amount, payment and due date information</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="title">
                        Title <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-heading icon"></i>

                        <input type="text"
                               name="title"
                               id="title"
                               value="{{ old('title') }}"
                               required
                               placeholder="Example: First Installment"
                               class="field-input {{ $errors->has('title') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('title'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('title') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="amount">
                        Amount <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-rupee-sign icon"></i>

                        <input type="number"
                               step="0.01"
                               min="0"
                               name="amount"
                               id="amount"
                               value="{{ old('amount') }}"
                               required
                               placeholder="Enter installment amount"
                               class="field-input {{ $errors->has('amount') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('amount'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('amount') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="paid_amount">
                        Paid Amount
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-wallet icon"></i>

                        <input type="number"
                               step="0.01"
                               min="0"
                               name="paid_amount"
                               id="paid_amount"
                               value="{{ old('paid_amount', 0) }}"
                               placeholder="Enter paid amount"
                               class="field-input {{ $errors->has('paid_amount') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('paid_amount'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('paid_amount') }}
                        </p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="due_date">
                        Due Date
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar-alt icon"></i>

                        <input type="date"
                               name="due_date"
                               id="due_date"
                               value="{{ old('due_date') }}"
                               class="field-input {{ $errors->has('due_date') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('due_date'))
                        <p class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first('due_date') }}
                        </p>
                    @endif
                </div>

                <input type="hidden" name="status" value="{{ old('status', 'pending') }}">

            </div>
        </div>

        <div class="form-card" style="grid-column:1/-1;">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-calculator"></i>
                </div>

                <div>
                    <p class="form-card-title">Payment Preview</p>
                    <p class="form-card-subtitle">Auto calculated installment summary</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="stats-grid" style="margin-bottom:0;">
                    <div class="stat-card">
                        <p class="stat-label">Installment Amount</p>
                        <p class="stat-value" id="previewAmount">₹0.00</p>
                    </div>

                    <div class="stat-card">
                        <p class="stat-label">Paid Amount</p>
                        <p class="stat-value" id="previewPaid">₹0.00</p>
                    </div>

                    <div class="stat-card">
                        <p class="stat-label">Due Amount</p>
                        <p class="stat-value" id="previewDue">₹0.00</p>
                    </div>

                    <div class="stat-card">
                        <p class="stat-label">Status</p>
                        <p class="stat-value" id="previewStatus" style="font-size:22px;">Pending</p>
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

        <a href="{{ route('admin.fee-installments.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</form>

@endsection

@section('scripts')
@parent
<script>
function updateInstallmentPreview() {
    const amountInput = document.getElementById('amount');
    const paidInput = document.getElementById('paid_amount');

    const amount = parseFloat(amountInput ? amountInput.value || 0 : 0);
    const paid = parseFloat(paidInput ? paidInput.value || 0 : 0);
    const due = Math.max(amount - paid, 0);

    let status = 'Pending';

    if (amount > 0 && paid >= amount) {
        status = 'Paid';
    } else if (paid > 0 && paid < amount) {
        status = 'Partial';
    }

    document.getElementById('previewAmount').innerText = '₹' + amount.toFixed(2);
    document.getElementById('previewPaid').innerText = '₹' + paid.toFixed(2);
    document.getElementById('previewDue').innerText = '₹' + due.toFixed(2);
    document.getElementById('previewStatus').innerText = status;
}

document.addEventListener('DOMContentLoaded', function () {
    const amountInput = document.getElementById('amount');
    const paidInput = document.getElementById('paid_amount');

    if (amountInput) {
        amountInput.addEventListener('input', updateInstallmentPreview);
    }

    if (paidInput) {
        paidInput.addEventListener('input', updateInstallmentPreview);
    }

    updateInstallmentPreview();
});
</script>
@endsection