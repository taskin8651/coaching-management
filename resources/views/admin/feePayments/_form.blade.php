@php
    $isEdit = (bool) $feePayment;
    $val = fn ($field, $default = null) => old($field, $isEdit ? $feePayment->{$field} : $default);
@endphp

<div class="admin-form-grid">

    {{-- STUDENT MAPPING --}}
    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-icon"><i class="fas fa-user-graduate"></i></div>
            <div>
                <p class="form-card-title">Student Mapping</p>
                <p class="form-card-subtitle">Select student, fee structure, branch, course and batch</p>
            </div>
        </div>

        <div class="form-card-body">

            <div class="field-group">
                <label class="field-label" for="student_id">Student <span class="req">*</span></label>
                <div class="input-icon-wrap">
                    <i class="fas fa-user-graduate icon"></i>
                    <select name="student_id" id="student_id" required class="field-input {{ $errors->has('student_id') ? 'error' : '' }}">
                        @foreach($students as $id => $student)
                            <option value="{{ $id }}" {{ $val('student_id') == $id ? 'selected' : '' }}>{{ $student }}</option>
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
                            <option value="{{ $id }}" {{ $val('fee_structure_id') == $id ? 'selected' : '' }}>{{ $feeStructure }}</option>
                        @endforeach
                    </select>
                </div>
                @if($errors->has('fee_structure_id'))
                    <p class="field-error">{{ $errors->first('fee_structure_id') }}</p>
                @else
                    <p class="field-hint">Select fee structure to auto-fill total fee.</p>
                @endif
            </div>

            <div class="field-group">
                <label class="field-label" for="fee_installment_id">Installment</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-layer-group icon"></i>
                    <select name="fee_installment_id" id="fee_installment_id" class="field-input {{ $errors->has('fee_installment_id') ? 'error' : '' }}">
                        @if($isEdit && $feePayment->feeInstallment)
                            <option value="{{ $feePayment->fee_installment_id }}" selected>
                                {{ $feePayment->feeInstallment->title }} — Due ₹{{ number_format($feePayment->feeInstallment->due_amount, 0) }}
                            </option>
                        @else
                            <option value="">Optional</option>
                        @endif
                    </select>
                </div>
                @if($errors->has('fee_installment_id'))
                    <p class="field-error">{{ $errors->first('fee_installment_id') }}</p>
                @else
                    <p class="field-hint">If this payment is settling a specific installment, link it here so that installment's due amount updates and the Fee Account auto-fills.</p>
                @endif
            </div>

            @can('fee_payment_allocate')
                <div class="field-group" style="grid-column: 1 / -1;">
                    <label class="field-label">
                        <input type="checkbox" name="allocate_multiple" value="1" id="allocate_multiple" {{ $val('allocate_multiple') || old('allocations') ? 'checked' : '' }}>
                        Allocate this payment across multiple installments
                    </label>
                </div>

                <div id="allocationSection" style="grid-column: 1 / -1; display:none;">
                    <div id="allocationRows"></div>
                    <button type="button" class="btn-mini-primary" onclick="addAllocationRow()">
                        <i class="fas fa-plus"></i> Add Installment
                    </button>
                    <p class="field-hint mt-2">Any part of the paid amount left unallocated is automatically recorded as advance/credit for the student.</p>
                </div>
            @endcan

            <div class="field-group">
                <label class="field-label" for="branch_id">Branch</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-school icon"></i>
                    <select name="branch_id" id="branch_id" class="field-input {{ $errors->has('branch_id') ? 'error' : '' }}">
                        @foreach($branches as $id => $branch)
                            <option value="{{ $id }}" {{ $val('branch_id') == $id ? 'selected' : '' }}>{{ $branch }}</option>
                        @endforeach
                    </select>
                </div>
                @if($errors->has('branch_id')) <p class="field-error">{{ $errors->first('branch_id') }}</p> @endif
            </div>

            <div class="field-group">
                <label class="field-label" for="batch_id">Batch</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-users icon"></i>
                    <select name="batch_id" id="batch_id" class="field-input {{ $errors->has('batch_id') ? 'error' : '' }}">
                        @foreach($batches as $id => $batch)
                            <option value="{{ $id }}" {{ $val('batch_id') == $id ? 'selected' : '' }}>{{ $batch }}</option>
                        @endforeach
                    </select>
                </div>
                @if($errors->has('batch_id')) <p class="field-error">{{ $errors->first('batch_id') }}</p> @endif
            </div>

            <div class="field-group">
                <label class="field-label" for="course_id">Course</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-book icon"></i>
                    <select name="course_id" id="course_id" class="field-input {{ $errors->has('course_id') ? 'error' : '' }}">
                        @foreach($courses as $id => $course)
                            <option value="{{ $id }}" {{ $val('course_id') == $id ? 'selected' : '' }}>{{ $course }}</option>
                        @endforeach
                    </select>
                </div>
                @if($errors->has('course_id')) <p class="field-error">{{ $errors->first('course_id') }}</p> @endif
            </div>

            <div class="field-group">
                <label class="field-label" for="fee_account_id">Fee Account <span class="req">*</span></label>
                <div class="input-icon-wrap">
                    <i class="fas fa-university icon"></i>
                    <select name="fee_account_id" id="fee_account_id" required class="field-input {{ $errors->has('fee_account_id') ? 'error' : '' }}">
                        @foreach($feeAccounts as $id => $account)
                            <option value="{{ $id }}" {{ $val('fee_account_id') == $id ? 'selected' : '' }}>{{ $account }}</option>
                        @endforeach
                    </select>
                </div>
                @if($errors->has('fee_account_id'))
                    <p class="field-error">{{ $errors->first('fee_account_id') }}</p>
                @else
                    <p class="field-hint">Account this payment is collected into — auto-suggested from the selected installment.</p>
                @endif
            </div>

            @if($isEdit && $feePayment->concession)
                <div class="field-group">
                    <label class="field-label">Concession</label>
                    <input type="hidden" name="concession_id" value="{{ $feePayment->concession_id }}">
                    <input type="text" class="field-input" value="{{ $feePayment->concession->type }} ({{ $feePayment->concession->amount_type == 'percentage' ? $feePayment->concession->percentage . '%' : '₹' . number_format($feePayment->concession->amount, 2) }})" readonly>
                </div>
            @endif

        </div>
    </div>

    {{-- PAYMENT DETAILS --}}
    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-icon"><i class="fas fa-rupee-sign"></i></div>
            <div>
                <p class="form-card-title">Payment Details</p>
                <p class="form-card-subtitle">Amount calculation and payment mode</p>
            </div>
        </div>

        <div class="form-card-body">

            <div class="field-group">
                <label class="field-label" for="receipt_no">Receipt No</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-receipt icon"></i>
                    <input type="text" name="receipt_no" id="receipt_no" value="{{ $val('receipt_no') }}" placeholder="Auto generated if blank" class="field-input {{ $errors->has('receipt_no') ? 'error' : '' }}">
                </div>
                @if($errors->has('receipt_no'))
                    <p class="field-error">{{ $errors->first('receipt_no') }}</p>
                @else
                    <p class="field-hint">Leave blank for auto receipt number (Branch/AcademicYear/Sequence).</p>
                @endif
            </div>

            <div class="field-group">
                <label class="field-label" for="total_fee">Total Fee <span class="req">*</span></label>
                <div class="input-icon-wrap">
                    <i class="fas fa-rupee-sign icon"></i>
                    <input type="number" step="0.01" min="0" name="total_fee" id="total_fee" value="{{ $val('total_fee', 0) }}" required class="field-input {{ $errors->has('total_fee') ? 'error' : '' }}">
                </div>
                @if($errors->has('total_fee')) <p class="field-error">{{ $errors->first('total_fee') }}</p> @endif
            </div>

            <div class="field-group">
                <label class="field-label" for="discount">Discount</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-tags icon"></i>
                    <input type="number" step="0.01" min="0" name="discount" id="discount" value="{{ $val('discount', 0) }}" class="field-input {{ $errors->has('discount') ? 'error' : '' }}">
                </div>
                @if($errors->has('discount')) <p class="field-error">{{ $errors->first('discount') }}</p> @endif
            </div>

            <div class="field-group">
                <label class="field-label" for="paid_amount">Paid Amount <span class="req">*</span></label>
                <div class="input-icon-wrap">
                    <i class="fas fa-money-bill-wave icon"></i>
                    <input type="number" step="0.01" min="0" name="paid_amount" id="paid_amount" value="{{ $val('paid_amount', 0) }}" required class="field-input {{ $errors->has('paid_amount') ? 'error' : '' }}">
                </div>
                @if($errors->has('paid_amount')) <p class="field-error">{{ $errors->first('paid_amount') }}</p> @endif
            </div>

            <div class="field-group">
                <label class="field-label" for="payment_mode">Payment Mode <span class="req">*</span></label>
                <div class="input-icon-wrap">
                    <i class="fas fa-credit-card icon"></i>
                    <select name="payment_mode" id="payment_mode" required class="field-input {{ $errors->has('payment_mode') ? 'error' : '' }}">
                        @foreach($paymentModes as $key => $mode)
                            <option value="{{ $key }}" {{ $val('payment_mode', 'cash') == $key ? 'selected' : '' }}>{{ $mode }}</option>
                        @endforeach
                    </select>
                </div>
                @if($errors->has('payment_mode')) <p class="field-error">{{ $errors->first('payment_mode') }}</p> @endif
            </div>

            <div class="field-group mode-field mode-cheque">
                <label class="field-label">Cheque Number</label>
                <input type="text" name="cheque_number" value="{{ $val('cheque_number') }}" class="field-input {{ $errors->has('cheque_number') ? 'error' : '' }}">
            </div>
            <div class="field-group mode-field mode-cheque">
                <label class="field-label">Cheque Date</label>
                <input type="date" name="cheque_date" value="{{ $val('cheque_date') ? \Illuminate\Support\Carbon::parse($val('cheque_date'))->format('Y-m-d') : '' }}" class="field-input {{ $errors->has('cheque_date') ? 'error' : '' }}">
            </div>
            <div class="field-group mode-field mode-cheque">
                <label class="field-label">Cheque Bank Name</label>
                <input type="text" name="cheque_bank_name" value="{{ $val('cheque_bank_name') }}" class="field-input">
            </div>

            <div class="field-group mode-field mode-upi">
                <label class="field-label">UPI Transaction Ref</label>
                <input type="text" name="upi_txn_ref" value="{{ $val('upi_txn_ref') }}" class="field-input {{ $errors->has('upi_txn_ref') ? 'error' : '' }}">
            </div>

            <div class="field-group mode-field mode-bank_transfer">
                <label class="field-label">NEFT/RTGS/IMPS UTR</label>
                <input type="text" name="neft_rtgs_imps_utr" value="{{ $val('neft_rtgs_imps_utr') }}" class="field-input {{ $errors->has('neft_rtgs_imps_utr') ? 'error' : '' }}">
            </div>
            <div class="field-group mode-field mode-bank_transfer">
                <label class="field-label">Bank Name</label>
                <input type="text" name="neft_rtgs_imps_bank_name" value="{{ $val('neft_rtgs_imps_bank_name') }}" class="field-input">
            </div>

            <div class="field-group mode-field mode-card">
                <label class="field-label">Card / Gateway Reference</label>
                <input type="text" name="card_gateway_ref" value="{{ $val('card_gateway_ref') }}" class="field-input {{ $errors->has('card_gateway_ref') ? 'error' : '' }}">
            </div>

            <div class="field-group mode-field mode-other">
                <label class="field-label">Reference / Remarks</label>
                <input type="text" name="other_reference" value="{{ $val('other_reference') }}" class="field-input {{ $errors->has('other_reference') ? 'error' : '' }}">
            </div>

            <div class="field-group">
                <label class="field-label" for="payment_date">Payment Date</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-calendar icon"></i>
                    <input type="date" name="payment_date" id="payment_date" value="{{ $val('payment_date', date('Y-m-d')) ? \Illuminate\Support\Carbon::parse($val('payment_date', date('Y-m-d')))->format('Y-m-d') : '' }}" class="field-input {{ $errors->has('payment_date') ? 'error' : '' }}">
                </div>
                @if($errors->has('payment_date')) <p class="field-error">{{ $errors->first('payment_date') }}</p> @endif
            </div>

            <div class="field-group">
                <label class="field-label" for="collected_by_id">Collected By</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-user-tie icon"></i>
                    <select name="collected_by_id" id="collected_by_id" class="field-input {{ $errors->has('collected_by_id') ? 'error' : '' }}">
                        @foreach($users as $id => $user)
                            <option value="{{ $id }}" {{ $val('collected_by_id', auth()->id()) == $id ? 'selected' : '' }}>{{ $user }}</option>
                        @endforeach
                    </select>
                </div>
                @if($errors->has('collected_by_id')) <p class="field-error">{{ $errors->first('collected_by_id') }}</p> @endif
            </div>

        </div>
    </div>

    {{-- GST --}}
    <div class="form-card" style="grid-column: 1 / -1;">
        <div class="form-card-header">
            <div class="form-card-icon"><i class="fas fa-file-invoice"></i></div>
            <div>
                <p class="form-card-title">GST</p>
                <p class="form-card-subtitle">Confirm GST for this payment, if applicable — not auto-calculated</p>
            </div>
        </div>

        <div class="form-card-body">
            <div class="field-group">
                <label class="field-label">
                    <input type="checkbox" name="gst_applicable" value="1" id="gst_applicable" {{ $val('gst_applicable') ? 'checked' : '' }}>
                    Apply GST to this payment
                </label>
            </div>

            <div class="field-group">
                <label class="field-label">GST %</label>
                <input type="number" step="0.01" min="0" max="100" name="gst_percent" id="gst_percent" value="{{ $val('gst_percent', 0) }}" class="field-input">
            </div>

            <div class="field-group">
                <label class="field-label">GST Amount</label>
                <input type="number" step="0.01" min="0" name="gst_amount" id="gst_amount" value="{{ $val('gst_amount', 0) }}" class="field-input">
            </div>
        </div>
    </div>

    {{-- LIVE CALCULATION --}}
    <div class="form-card" style="grid-column: 1 / -1;">
        <div class="form-card-header">
            <div class="form-card-icon"><i class="fas fa-calculator"></i></div>
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

            <div class="field-group">
                <label class="field-label" for="remarks">Remarks</label>
                <textarea name="remarks" id="remarks" rows="4" placeholder="Enter payment remarks" class="field-input {{ $errors->has('remarks') ? 'error' : '' }}">{{ $val('remarks') }}</textarea>
                @if($errors->has('remarks')) <p class="field-error">{{ $errors->first('remarks') }}</p> @endif
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

<script>
const feeStructures = @json($feeStructureData);
const feeAccountsById = @json($feeAccounts);
const installmentsByStudentGlobal = @json($installmentsByStudent);
const allCourses = @json($courses);

function installmentOptionsForCurrentStudent(selectedId) {
    const studentId = document.getElementById('student_id').value;
    const rows = installmentsByStudentGlobal[studentId] || [];

    return rows.map(function (row) {
        const sel = String(row.id) === String(selectedId ?? '') ? 'selected' : '';
        return `<option value="${row.id}" ${sel}>${row.name}</option>`;
    }).join('');
}

function buildAllocationRow(allocation) {
    allocation = allocation || {};
    const row = document.createElement('div');
    row.className = 'item-row';
    row.style.gridTemplateColumns = '2fr 1fr 44px';

    row.innerHTML = `
        <div class="field-group mb-0">
            <label class="field-label">Installment</label>
            <select name="allocations[][fee_installment_id]" class="field-input">
                ${installmentOptionsForCurrentStudent(allocation.fee_installment_id)}
            </select>
        </div>
        <div class="field-group mb-0">
            <label class="field-label">Amount</label>
            <input type="number" step="0.01" min="0.01" name="allocations[][amount]" class="field-input" value="${allocation.amount ?? ''}">
        </div>
        <button type="button" class="row-remove" onclick="this.closest('.item-row').remove()"><i class="fas fa-times"></i></button>
    `;

    return row;
}

function addAllocationRow() {
    document.getElementById('allocationRows').appendChild(buildAllocationRow());
}

function toggleAllocationSection() {
    const checkbox = document.getElementById('allocate_multiple');
    if (!checkbox) return;

    const on = checkbox.checked;
    document.getElementById('allocationSection').style.display = on ? '' : 'none';

    const installmentGroup = document.getElementById('fee_installment_id').closest('.field-group');
    installmentGroup.style.display = on ? 'none' : '';

    if (on && document.getElementById('allocationRows').children.length === 0) {
        addAllocationRow();
    }
}

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

    if (payablePreview) payablePreview.innerText = '₹' + payable.toFixed(2);
    if (duePreview) duePreview.innerText = '₹' + due.toFixed(2);
}

function applyFeeStructure() {
    const feeStructureSelect = document.getElementById('fee_structure_id');
    if (!feeStructureSelect) return;

    const id = feeStructureSelect.value;
    if (!id || !feeStructures[id]) return;

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

function toggleModeFields() {
    const mode = document.getElementById('payment_mode').value;

    document.querySelectorAll('.mode-field').forEach(function (el) {
        el.style.display = el.classList.contains('mode-' + mode) ? '' : 'none';
    });
}

function toggleGstFields() {
    const on = document.getElementById('gst_applicable').checked;
    document.getElementById('gst_percent').closest('.field-group').style.display = on ? '' : 'none';
    document.getElementById('gst_amount').closest('.field-group').style.display = on ? '' : 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    ['total_fee', 'discount', 'paid_amount'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', updateFeePreview);
    });

    const branchSelect = document.getElementById('branch_id');
    const courseSelect = document.getElementById('course_id');
    const batchSelect = document.getElementById('batch_id');
    const batchesByBranch = @json($batchesByBranch);
    const coursesByBatch = @json($coursesByBatch);

    cascadeByParent(batchSelect, branchSelect, batchesByBranch, {
        placeholder: @json(trans('global.pleaseSelect')),
        keepValue: @json(old('batch_id', $isEdit ? $feePayment->batch_id : null)),
    });

    cascadeByParent(courseSelect, batchSelect, coursesByBatch, {
        placeholder: @json(trans('global.pleaseSelect')),
        keepValue: @json(old('course_id', $isEdit ? $feePayment->course_id : null)),
    });

    const feeStructureSelect = document.getElementById('fee_structure_id');
    if (feeStructureSelect) feeStructureSelect.addEventListener('change', applyFeeStructure);

    const installmentSelect = document.getElementById('fee_installment_id');
    const installmentsByStudent = @json($installmentsByStudent);
    const feeAccountSelect = document.getElementById('fee_account_id');

    cascadeByParent(installmentSelect, document.getElementById('student_id'), installmentsByStudent, {
        placeholder: 'Optional',
        keepValue: @json(old('fee_installment_id', $isEdit ? $feePayment->fee_installment_id : null)),
    });

    if (installmentSelect && feeAccountSelect) {
        installmentSelect.addEventListener('change', function () {
            const studentId = document.getElementById('student_id').value;
            const rows = installmentsByStudent[studentId] || [];
            const row = rows.find(r => String(r.id) === String(this.value));

            if (row && row.fee_account_id && feeAccountsById[row.fee_account_id]) {
                feeAccountSelect.value = row.fee_account_id;
            }
        });
    }

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
            if (!details) return;

            if (details.branch_id) {
                branchSelect.value = details.branch_id;
                branchSelect.dispatchEvent(new Event('change'));
            }
            if (details.batch_id) {
                batchSelect.value = details.batch_id;
                batchSelect.dispatchEvent(new Event('change'));
            }

            // course_id's <option> list is normally rebuilt from the SELECTED BATCH's own
            // course (cascadeByParent above), so if this student has no batch assigned yet, or
            // their own course_id doesn't match that batch's course, the option we need doesn't
            // exist yet and setting .value silently does nothing. Make sure the option exists
            // (adding it from the student's own course_id if missing) before selecting it — a
            // student's course_id is authoritative regardless of batch assignment.
            if (details.course_id) {
                let courseOption = courseSelect.querySelector('option[value="' + details.course_id + '"]');

                if (!courseOption && allCourses[details.course_id]) {
                    courseOption = new Option(allCourses[details.course_id], details.course_id);
                    courseSelect.appendChild(courseOption);
                }

                if (courseOption) {
                    courseSelect.value = details.course_id;
                }
            }

            const structureId = matchingFeeStructureId(details.branch_id, details.course_id, details.batch_id);
            if (structureId && feeStructureSelect) {
                feeStructureSelect.value = structureId;
                applyFeeStructure();
            }
        });
    }

    document.getElementById('payment_mode').addEventListener('change', toggleModeFields);
    document.getElementById('gst_applicable').addEventListener('change', toggleGstFields);

    const allocateCheckbox = document.getElementById('allocate_multiple');

    if (allocateCheckbox) {
        allocateCheckbox.addEventListener('change', toggleAllocationSection);

        const initialAllocations = @json(old('allocations') ? collect(old('allocations'))->values() : collect());
        initialAllocations.forEach(function (allocation) {
            document.getElementById('allocationRows').appendChild(buildAllocationRow(allocation));
        });

        toggleAllocationSection();
    }

    toggleModeFields();
    toggleGstFields();
    updateFeePreview();
});
</script>
