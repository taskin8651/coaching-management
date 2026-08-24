@extends('layouts.admin')

@section('page-title', 'Edit Fee Payment')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.fee-payments.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Edit Fee Payment</h2>

        <p class="admin-page-subtitle">
            Update fee payment, receipt and amount details
        </p>
    </div>

    <div class="identity-card">
        <div class="identity-avatar" style="background:#10B981;">
            <i class="fas fa-rupee-sign"></i>
        </div>

        <div>
            <p class="identity-title">{{ $feePayment->receipt_no ?? 'Receipt' }}</p>
            <p class="identity-subtitle">ID #{{ $feePayment->id }}</p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.fee-payments.update', $feePayment->id) }}">
    @method('PUT')
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>

                <div>
                    <p class="form-card-title">Student Mapping</p>
                    <p class="form-card-subtitle">Update student, branch, course and batch</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="student_id">Student</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-user-graduate icon"></i>

                        <select name="student_id" id="student_id" class="field-input {{ $errors->has('student_id') ? 'error' : '' }}">
                            @foreach($students as $id => $student)
                                <option value="{{ $id }}" {{ old('student_id', $feePayment->student_id) == $id ? 'selected' : '' }}>
                                    {{ $student }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('student_id'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('student_id') }}</p>
                    @else
                        <p class="field-hint">Selecting a student auto-fills branch, course, batch and matching fee structure below.</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="fee_structure_id">Fee Structure</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-list-alt icon"></i>

                        <select name="fee_structure_id" id="fee_structure_id" class="field-input {{ $errors->has('fee_structure_id') ? 'error' : '' }}">
                            @foreach($feeStructures as $id => $feeStructure)
                                <option value="{{ $id }}" {{ old('fee_structure_id', $feePayment->fee_structure_id) == $id ? 'selected' : '' }}>
                                    {{ $feeStructure }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('fee_structure_id'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('fee_structure_id') }}</p>
                    @else
                        <p class="field-hint">Select fee structure to auto-fill total fee.</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="fee_installment_id">Installment</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-layer-group icon"></i>

                        <select name="fee_installment_id" id="fee_installment_id" class="field-input {{ $errors->has('fee_installment_id') ? 'error' : '' }}">
                            <option value="">Optional</option>
                        </select>
                    </div>

                    @if($errors->has('fee_installment_id'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('fee_installment_id') }}</p>
                    @else
                        <p class="field-hint">If this payment is settling a specific installment, link it here so that installment's due amount updates.</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="branch_id">Branch</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-school icon"></i>

                        <select name="branch_id" id="branch_id" class="field-input {{ $errors->has('branch_id') ? 'error' : '' }}">
                            @foreach($branches as $id => $branch)
                                <option value="{{ $id }}" {{ old('branch_id', $feePayment->branch_id) == $id ? 'selected' : '' }}>
                                    {{ $branch }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('branch_id'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('branch_id') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="batch_id">Batch</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-users icon"></i>

                        <select name="batch_id" id="batch_id" class="field-input {{ $errors->has('batch_id') ? 'error' : '' }}">
                            @foreach($batches as $id => $batch)
                                <option value="{{ $id }}" {{ old('batch_id', $feePayment->batch_id) == $id ? 'selected' : '' }}>
                                    {{ $batch }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('batch_id'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('batch_id') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="course_id">Course</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-book icon"></i>

                        <select name="course_id" id="course_id" class="field-input {{ $errors->has('course_id') ? 'error' : '' }}">
                            @foreach($courses as $id => $course)
                                <option value="{{ $id }}" {{ old('course_id', $feePayment->course_id) == $id ? 'selected' : '' }}>
                                    {{ $course }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('course_id'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('course_id') }}</p>
                    @endif
                </div>

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-rupee-sign"></i>
                </div>

                <div>
                    <p class="form-card-title">Payment Details</p>
                    <p class="form-card-subtitle">Update amount calculation and payment mode</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="receipt_no">Receipt No</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-receipt icon"></i>

                        <input type="text"
                               name="receipt_no"
                               id="receipt_no"
                               value="{{ old('receipt_no', $feePayment->receipt_no) }}"
                               class="field-input {{ $errors->has('receipt_no') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('receipt_no'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('receipt_no') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="total_fee">Total Fee <span class="req">*</span></label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-rupee-sign icon"></i>

                        <input type="number"
                               step="0.01"
                               min="0"
                               name="total_fee"
                               id="total_fee"
                               value="{{ old('total_fee', $feePayment->total_fee) }}"
                               required
                               class="field-input {{ $errors->has('total_fee') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('total_fee'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('total_fee') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="discount">Discount</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-tags icon"></i>

                        <input type="number"
                               step="0.01"
                               min="0"
                               name="discount"
                               id="discount"
                               value="{{ old('discount', $feePayment->discount) }}"
                               class="field-input {{ $errors->has('discount') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('discount'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('discount') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="paid_amount">Paid Amount <span class="req">*</span></label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-money-bill-wave icon"></i>

                        <input type="number"
                               step="0.01"
                               min="0"
                               name="paid_amount"
                               id="paid_amount"
                               value="{{ old('paid_amount', $feePayment->paid_amount) }}"
                               required
                               class="field-input {{ $errors->has('paid_amount') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('paid_amount'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('paid_amount') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="payment_mode">Payment Mode <span class="req">*</span></label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-credit-card icon"></i>

                        <select name="payment_mode" id="payment_mode" required class="field-input {{ $errors->has('payment_mode') ? 'error' : '' }}">
                            @foreach($paymentModes as $key => $mode)
                                <option value="{{ $key }}" {{ old('payment_mode', $feePayment->payment_mode) == $key ? 'selected' : '' }}>
                                    {{ $mode }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('payment_mode'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('payment_mode') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="payment_status">Payment Status</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-toggle-on icon"></i>

                        <select name="payment_status" id="payment_status" class="field-input {{ $errors->has('payment_status') ? 'error' : '' }}">
                            <option value="paid" {{ old('payment_status', $feePayment->payment_status) == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="partial" {{ old('payment_status', $feePayment->payment_status) == 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="due" {{ old('payment_status', $feePayment->payment_status) == 'due' ? 'selected' : '' }}>Due</option>
                            <option value="cancelled" {{ old('payment_status', $feePayment->payment_status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    @if($errors->has('payment_status'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('payment_status') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="payment_date">Payment Date</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar icon"></i>

                        <input type="date"
                               name="payment_date"
                               id="payment_date"
                               value="{{ old('payment_date', $feePayment->payment_date ? \Carbon\Carbon::parse($feePayment->payment_date)->format('Y-m-d') : '') }}"
                               class="field-input {{ $errors->has('payment_date') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('payment_date'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('payment_date') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="collected_by_id">Collected By</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-user-tie icon"></i>

                        <select name="collected_by_id" id="collected_by_id" class="field-input {{ $errors->has('collected_by_id') ? 'error' : '' }}">
                            @foreach($users as $id => $user)
                                <option value="{{ $id }}" {{ old('collected_by_id', $feePayment->collected_by_id) == $id ? 'selected' : '' }}>
                                    {{ $user }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('collected_by_id'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('collected_by_id') }}</p>
                    @endif
                </div>

            </div>
        </div>

        <div class="form-card" style="grid-column: 1 / -1;">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-calculator"></i>
                </div>

                <div>
                    <p class="form-card-title">Live Calculation</p>
                    <p class="form-card-subtitle">Backend will recalculate payable and due amount.</p>
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

                <div class="field-group">
                    <label class="field-label" for="remarks">Remarks</label>

                    <textarea name="remarks"
                              id="remarks"
                              rows="4"
                              class="field-input {{ $errors->has('remarks') ? 'error' : '' }}">{{ old('remarks', $feePayment->remarks) }}</textarea>

                    @if($errors->has('remarks'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('remarks') }}</p>
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
        branch.dispatchEvent(new Event('change'));
    }

    if (batch && data.batch_id) {
        batch.value = data.batch_id;
        batch.dispatchEvent(new Event('change'));
    }

    if (course && data.course_id) {
        course.value = data.course_id;
    }

    if (total && data.total_fee) {
        total.value = parseFloat(data.total_fee).toFixed(2);
    }

    updateFeePreview();
}

function updateFeePreview() {
    const total = parseFloat(document.getElementById('total_fee').value || 0);
    const discount = parseFloat(document.getElementById('discount').value || 0);
    const paid = parseFloat(document.getElementById('paid_amount').value || 0);

    const payable = Math.max(total - discount, 0);
    const due = Math.max(payable - paid, 0);

    document.getElementById('payablePreview').innerText = '₹' + payable.toFixed(2);
    document.getElementById('duePreview').innerText = '₹' + due.toFixed(2);
}

document.addEventListener('DOMContentLoaded', function () {
    ['total_fee', 'discount', 'paid_amount'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', updateFeePreview);
        }
    });

    const branchSelect = document.getElementById('branch_id');
    const courseSelect = document.getElementById('course_id');
    const batchSelect = document.getElementById('batch_id');
    const batchesByBranch = @json($batchesByBranch);
    const coursesByBatch = @json($coursesByBatch);

    cascadeByParent(batchSelect, branchSelect, batchesByBranch, {
        placeholder: @json(trans('global.pleaseSelect')),
        keepValue: @json(old('batch_id', $feePayment->batch_id)),
    });

    cascadeByParent(courseSelect, batchSelect, coursesByBatch, {
        placeholder: @json(trans('global.pleaseSelect')),
        keepValue: @json(old('course_id', $feePayment->course_id)),
    });

    const feeStructureSelect = document.getElementById('fee_structure_id');

    if (feeStructureSelect) {
        feeStructureSelect.addEventListener('change', applyFeeStructure);
    }

    const installmentsByStudent = @json($installmentsByStudent);

    cascadeByParent(document.getElementById('fee_installment_id'), document.getElementById('student_id'), installmentsByStudent, {
        placeholder: 'Optional',
        keepValue: @json(old('fee_installment_id', $feePayment->fee_installment_id)),
    });

    const studentDetails = @json($studentDetails);
    const studentSelect = document.getElementById('student_id');

    function matchingFeeStructureId(branchId, courseId, batchId) {
        return Object.keys(feeStructures).find(function (id) {
            const structure = feeStructures[id];

            return String(structure.branch_id) === String(branchId)
                && String(structure.course_id) === String(courseId)
                && String(structure.batch_id) === String(batchId);
        });
    }

    if (studentSelect) {
        studentSelect.addEventListener('change', function () {
            const details = studentDetails[this.value];

            if (!details) {
                return;
            }

            if (details.branch_id) {
                branchSelect.value = details.branch_id;
                branchSelect.dispatchEvent(new Event('change'));
            }

            if (details.batch_id) {
                batchSelect.value = details.batch_id;
                batchSelect.dispatchEvent(new Event('change'));
            }

            if (details.course_id) {
                courseSelect.value = details.course_id;
            }

            const structureId = matchingFeeStructureId(details.branch_id, details.course_id, details.batch_id);

            if (structureId && feeStructureSelect) {
                feeStructureSelect.value = structureId;
                applyFeeStructure();
            }
        });
    }

    updateFeePreview();
});
</script>
@endsection