@php
    $isEdit = (bool) $feeStructure;
    $hasLedgers = $hasLedgers ?? false;

    $initialItems = $isEdit
        ? $feeStructure->items->map(fn ($item) => [
            'fee_head_id' => $item->fee_head_id,
            'amount' => (float) $item->amount,
            'gst_applicable' => (bool) $item->gst_applicable,
            'gst_percent' => (float) $item->gst_percent,
        ])->values()
        : (old('items') ? collect(old('items'))->values() : collect());

    $initialInstallments = $isEdit
        ? $feeStructure->installmentTemplates->map(fn ($installment) => [
            'title' => $installment->title,
            'amount_type' => $installment->amount_type,
            'amount' => $installment->amount,
            'percentage' => $installment->percentage,
            'due_date' => optional($installment->due_date)->format('Y-m-d'),
            'fee_account_id' => $installment->fee_account_id,
            'late_fee_enabled' => (bool) $installment->late_fee_enabled,
            'late_fee_type' => $installment->late_fee_type,
            'late_fee_amount' => $installment->late_fee_amount,
            'late_fee_percentage' => $installment->late_fee_percentage,
            'late_fee_grace_days' => $installment->late_fee_grace_days,
            'late_fee_max_amount' => $installment->late_fee_max_amount,
        ])->values()
        : (old('installments') ? collect(old('installments'))->values() : collect());
@endphp

@if($hasLedgers)
    <div class="form-info-box mb-3" style="border-color:#F59E0B;">
        <p>
            <i class="fas fa-exclamation-triangle"></i>
            Students are already assigned to this fee structure (v{{ $feeStructure->version_no }}). Saving changes here will
            create a <strong>new version</strong> instead of editing it directly — already-assigned students keep their
            current version untouched.
        </p>
    </div>
@endif

<div class="admin-form-grid">

    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-icon"><i class="fas fa-list-alt"></i></div>
            <div>
                <p class="form-card-title">Basic Details</p>
                <p class="form-card-subtitle">Branch, course, batch and academic year mapping</p>
            </div>
        </div>

        <div class="form-card-body">

            <div class="field-group">
                <label class="field-label">Title <span class="req">*</span></label>
                <input type="text" name="title" value="{{ old('title', $isEdit ? $feeStructure->title : '') }}"
                       placeholder="Example: Class 10 Annual Fee" required
                       class="field-input {{ $errors->has('title') ? 'error' : '' }}">
            </div>

            <div class="field-group">
                <label class="field-label">Academic Year <span class="req">*</span></label>
                <input type="text" name="academic_year" value="{{ old('academic_year', $isEdit ? $feeStructure->academic_year : '') }}"
                       placeholder="Example: 2026-27" required
                       class="field-input {{ $errors->has('academic_year') ? 'error' : '' }}">
            </div>

            <div class="field-group">
                <label class="field-label">Branch <span class="req">*</span></label>
                <select name="branch_id" id="branch_id" required class="field-input">
                    @foreach($branches as $id => $branch)
                        <option value="{{ $id }}" {{ old('branch_id', $isEdit ? $feeStructure->branch_id : '') == $id ? 'selected' : '' }}>{{ $branch }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field-group">
                <label class="field-label">Course <span class="req">*</span></label>
                <select name="course_id" id="course_id" required class="field-input">
                    @foreach($courses as $id => $course)
                        <option value="{{ $id }}" {{ old('course_id', $isEdit ? $feeStructure->course_id : '') == $id ? 'selected' : '' }}>{{ $course }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field-group">
                <label class="field-label">Batch</label>
                <select name="batch_id" id="batch_id" class="field-input">
                    @foreach($batches as $id => $batch)
                        <option value="{{ $id }}" {{ old('batch_id', $isEdit ? $feeStructure->batch_id : '') == $id ? 'selected' : '' }}>{{ $batch }}</option>
                    @endforeach
                </select>
                <p class="field-hint">Empty means applicable for all batches of selected course.</p>
            </div>

            <div class="field-group">
                <label class="field-label">Board / Program</label>
                <input type="text" name="board" value="{{ old('board', $isEdit ? $feeStructure->board : '') }}" placeholder="Example: CBSE / ICSE" class="field-input">
            </div>

            <div class="field-group">
                <label class="field-label">Standard / Class</label>
                <input type="text" name="standard" value="{{ old('standard', $isEdit ? $feeStructure->standard : '') }}" placeholder="Example: Class 10" class="field-input">
            </div>

            <div class="field-group">
                <label class="field-label">Effective From <span class="req">*</span></label>
                <input type="date" name="effective_from" value="{{ old('effective_from', $isEdit ? optional($feeStructure->effective_from)->format('Y-m-d') : now()->format('Y-m-d')) }}" required class="field-input {{ $errors->has('effective_from') ? 'error' : '' }}">
            </div>

            <div class="field-group">
                <label class="field-label">Effective To</label>
                <input type="date" name="effective_to" value="{{ old('effective_to', $isEdit ? optional($feeStructure->effective_to)->format('Y-m-d') : '') }}" class="field-input {{ $errors->has('effective_to') ? 'error' : '' }}">
            </div>

            <div class="field-group">
                <label class="field-label">Status</label>
                <select name="status" class="field-input">
                    <option value="active" {{ old('status', $isEdit ? $feeStructure->status : 'active') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $isEdit ? $feeStructure->status : '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

        </div>
    </div>

    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-icon"><i class="fas fa-align-left"></i></div>
            <div>
                <p class="form-card-title">Description</p>
                <p class="form-card-subtitle">Optional notes for this fee structure</p>
            </div>
        </div>

        <div class="form-card-body">
            <div class="field-group">
                <label class="field-label">Description</label>
                <textarea name="description" rows="4" class="field-input">{{ old('description', $isEdit ? $feeStructure->description : '') }}</textarea>
            </div>

            <div class="field-group">
                <label class="field-label">
                    <input type="checkbox" name="installment_allocation_override" value="1" {{ old('installment_allocation_override', $isEdit ? $feeStructure->installment_allocation_override : false) ? 'checked' : '' }}>
                    Allow installment total mismatch
                </label>
                <p class="field-hint">By default installment amounts must add up to the fee total. Check this to override that check.</p>
            </div>
        </div>
    </div>

    <div class="form-card" style="grid-column: 1 / -1;">
        <div class="form-card-header between">
            <div class="form-card-head-left">
                <div class="form-card-icon"><i class="fas fa-rupee-sign"></i></div>
                <div>
                    <p class="form-card-title">Fee Line Items</p>
                    <p class="form-card-subtitle">Fee-head wise breakup — total is calculated automatically</p>
                </div>
            </div>

            <button type="button" class="btn-mini-primary" onclick="addItemRow()">
                <i class="fas fa-plus"></i> Add Fee Head
            </button>
        </div>

        <div class="form-card-body">
            <div id="itemRows"></div>

            <div class="form-info-box mt-3">
                <p>
                    <i class="fas fa-calculator"></i>
                    Fee Structure Total: <strong id="itemsTotalPreview">₹0.00</strong>
                </p>
            </div>
        </div>
    </div>

    <div class="form-card" style="grid-column: 1 / -1;">
        <div class="form-card-header between">
            <div class="form-card-head-left">
                <div class="form-card-icon"><i class="fas fa-calendar-alt"></i></div>
                <div>
                    <p class="form-card-title">Installment Plan</p>
                    <p class="form-card-subtitle">Optional — leave empty for a single one-time payment</p>
                </div>
            </div>

            <button type="button" class="btn-mini-primary" onclick="addInstallmentRow()">
                <i class="fas fa-plus"></i> Add Installment
            </button>
        </div>

        <div class="form-card-body">
            <div id="installmentRows"></div>

            <div class="form-info-box mt-3">
                <p>
                    <i class="fas fa-info-circle"></i>
                    Allocated: <strong id="installmentAllocatedPreview">₹0.00</strong>
                    of <strong id="installmentTotalPreview">₹0.00</strong>
                </p>
            </div>
        </div>
    </div>

</div>

<div class="form-actions">
    <button type="submit" class="btn-primary">
        <i class="fas fa-check"></i>
        {{ trans('global.save') }}
    </button>

    <a href="{{ route('admin.fee-structures.index') }}" class="btn-ghost">
        {{ trans('global.cancel') }}
    </a>
</div>

<style>
.item-row, .installment-row {
    display: grid;
    gap: 14px;
    padding: 14px;
    border-radius: 18px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    margin-bottom: 12px;
    align-items: end;
}

.item-row { grid-template-columns: 2fr 1fr 1fr 1fr 1fr 44px; }
.installment-row { grid-template-columns: 1.6fr 1fr 1fr 1fr 1.4fr 44px; }

.row-remove {
    width: 44px;
    height: 44px;
    border: none;
    border-radius: 14px;
    background: #FEE2E2;
    color: #991B1B;
    cursor: pointer;
}
.row-remove:hover { background: #EF4444; color: #fff; }

@media (max-width: 991px) {
    .item-row, .installment-row { grid-template-columns: 1fr; }
    .row-remove { width: 100%; }
}
</style>

<script>
const feeHeads = @json($feeHeads->map(fn ($h) => ['id' => $h->id, 'name' => $h->name, 'gst_applicable' => (bool) $h->gst_applicable, 'default_gst_percent' => (float) $h->default_gst_percent]));
const feeAccounts = @json($feeAccounts);
const initialItems = @json($initialItems);
const initialInstallments = @json($initialInstallments);

function feeHeadOptions(selectedId) {
    return feeHeads.map(function (h) {
        const sel = String(h.id) === String(selectedId ?? '') ? 'selected' : '';
        return `<option value="${h.id}" ${sel}>${h.name}</option>`;
    }).join('');
}

function feeAccountOptions(selectedId) {
    let html = '';
    for (const id in feeAccounts) {
        const sel = String(id) === String(selectedId ?? '') ? 'selected' : '';
        html += `<option value="${id}" ${sel}>${feeAccounts[id]}</option>`;
    }
    return html;
}

function buildItemRow(item) {
    item = item || {};
    const row = document.createElement('div');
    row.className = 'item-row';

    row.innerHTML = `
        <div class="field-group mb-0">
            <label class="field-label">Fee Head</label>
            <select name="items[][fee_head_id]" class="field-input item-fee-head" onchange="onFeeHeadChange(this)">
                ${feeHeadOptions(item.fee_head_id)}
            </select>
        </div>
        <div class="field-group mb-0">
            <label class="field-label">Amount</label>
            <input type="number" step="0.01" min="0" name="items[][amount]" class="field-input item-amount" value="${item.amount ?? 0}" oninput="recalculateItems()">
        </div>
        <div class="field-group mb-0">
            <label class="field-label">GST?</label>
            <select name="items[][gst_applicable]" class="field-input item-gst-applicable" onchange="recalculateItems()">
                <option value="0" ${!item.gst_applicable ? 'selected' : ''}>No</option>
                <option value="1" ${item.gst_applicable ? 'selected' : ''}>Yes</option>
            </select>
        </div>
        <div class="field-group mb-0">
            <label class="field-label">GST %</label>
            <input type="number" step="0.01" min="0" max="100" name="items[][gst_percent]" class="field-input item-gst-percent" value="${item.gst_percent ?? 0}" oninput="recalculateItems()">
        </div>
        <div class="field-group mb-0">
            <label class="field-label">Line Total</label>
            <input type="text" class="field-input item-line-total" value="0.00" readonly>
        </div>
        <button type="button" class="row-remove" onclick="removeRow(this, 'itemRows')"><i class="fas fa-times"></i></button>
    `;

    return row;
}

function onFeeHeadChange(select) {
    const headId = select.value;
    const head = feeHeads.find(h => String(h.id) === String(headId));
    const row = select.closest('.item-row');

    if (head) {
        row.querySelector('.item-gst-applicable').value = head.gst_applicable ? '1' : '0';
        row.querySelector('.item-gst-percent').value = head.default_gst_percent;
    }

    recalculateItems();
}

function recalculateItems() {
    let total = 0;

    document.querySelectorAll('#itemRows .item-row').forEach(function (row) {
        const amount = parseFloat(row.querySelector('.item-amount').value || 0);
        const gstOn = row.querySelector('.item-gst-applicable').value === '1';
        const gstPercent = gstOn ? parseFloat(row.querySelector('.item-gst-percent').value || 0) : 0;
        const gstAmount = gstOn ? Math.round(amount * gstPercent) / 100 : 0;
        const lineTotal = amount + gstAmount;

        row.querySelector('.item-line-total').value = lineTotal.toFixed(2);
        total += lineTotal;
    });

    document.getElementById('itemsTotalPreview').innerText = '₹' + total.toFixed(2);
    document.getElementById('installmentTotalPreview').innerText = '₹' + total.toFixed(2);

    recalculateInstallments(total);
}

function buildInstallmentRow(installment) {
    installment = installment || {};
    const row = document.createElement('div');
    row.className = 'installment-row';

    row.innerHTML = `
        <div class="field-group mb-0">
            <label class="field-label">Title</label>
            <input type="text" name="installments[][title]" class="field-input" placeholder="Example: Installment 1" value="${installment.title ?? ''}">
        </div>
        <div class="field-group mb-0">
            <label class="field-label">Type</label>
            <select name="installments[][amount_type]" class="field-input installment-type" onchange="onInstallmentTypeChange(this)">
                <option value="fixed" ${installment.amount_type !== 'percentage' ? 'selected' : ''}>Fixed ₹</option>
                <option value="percentage" ${installment.amount_type === 'percentage' ? 'selected' : ''}>Percentage %</option>
            </select>
        </div>
        <div class="field-group mb-0 installment-amount-group">
            <label class="field-label">Amount</label>
            <input type="number" step="0.01" min="0" name="installments[][amount]" class="field-input installment-amount" value="${installment.amount ?? ''}" oninput="recalculateItems()">
        </div>
        <div class="field-group mb-0 installment-percentage-group" style="display:none;">
            <label class="field-label">Percentage</label>
            <input type="number" step="0.01" min="0" max="100" name="installments[][percentage]" class="field-input installment-percentage" value="${installment.percentage ?? ''}" oninput="recalculateItems()">
        </div>
        <div class="field-group mb-0">
            <label class="field-label">Due Date</label>
            <input type="date" name="installments[][due_date]" class="field-input" value="${installment.due_date ?? ''}">
        </div>
        <button type="button" class="row-remove" onclick="removeRow(this, 'installmentRows')"><i class="fas fa-times"></i></button>
        <div class="field-group mb-0" style="grid-column: 1 / -1;">
            <label class="field-label">Fee Account</label>
            <select name="installments[][fee_account_id]" class="field-input">
                ${feeAccountOptions(installment.fee_account_id)}
            </select>
        </div>
        <div class="field-group mb-0" style="grid-column: 1 / -1;">
            <label class="field-label">
                <input type="checkbox" name="installments[][late_fee_enabled]" value="1" class="late-fee-enabled" ${installment.late_fee_enabled ? 'checked' : ''} onchange="onLateFeeToggle(this)">
                Late Fee
            </label>
        </div>
        <div class="late-fee-fields" style="display:${installment.late_fee_enabled ? 'grid' : 'none'}; grid-column: 1 / -1; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 14px;">
            <div class="field-group mb-0">
                <label class="field-label">Type</label>
                <select name="installments[][late_fee_type]" class="field-input late-fee-type" onchange="onLateFeeTypeChange(this)">
                    <option value="fixed" ${installment.late_fee_type !== 'percentage' && installment.late_fee_type !== 'per_day' ? 'selected' : ''}>Fixed ₹</option>
                    <option value="percentage" ${installment.late_fee_type === 'percentage' ? 'selected' : ''}>% of installment</option>
                    <option value="per_day" ${installment.late_fee_type === 'per_day' ? 'selected' : ''}>Per day ₹</option>
                </select>
            </div>
            <div class="field-group mb-0 late-fee-amount-group">
                <label class="field-label">Amount</label>
                <input type="number" step="0.01" min="0" name="installments[][late_fee_amount]" class="field-input" value="${installment.late_fee_amount ?? ''}">
            </div>
            <div class="field-group mb-0 late-fee-percentage-group" style="display:none;">
                <label class="field-label">Percentage</label>
                <input type="number" step="0.01" min="0" max="100" name="installments[][late_fee_percentage]" class="field-input" value="${installment.late_fee_percentage ?? ''}">
            </div>
            <div class="field-group mb-0">
                <label class="field-label">Grace Days</label>
                <input type="number" min="0" name="installments[][late_fee_grace_days]" class="field-input" value="${installment.late_fee_grace_days ?? 0}">
            </div>
            <div class="field-group mb-0">
                <label class="field-label">Max Cap (optional)</label>
                <input type="number" step="0.01" min="0" name="installments[][late_fee_max_amount]" class="field-input" value="${installment.late_fee_max_amount ?? ''}">
            </div>
        </div>
    `;

    onInstallmentTypeChange(row.querySelector('.installment-type'));
    onLateFeeTypeChange(row.querySelector('.late-fee-type'));

    return row;
}

function onLateFeeToggle(checkbox) {
    const row = checkbox.closest('.installment-row');
    row.querySelector('.late-fee-fields').style.display = checkbox.checked ? 'grid' : 'none';
}

function onLateFeeTypeChange(select) {
    const row = select.closest('.installment-row');
    const isPercentage = select.value === 'percentage';

    row.querySelector('.late-fee-amount-group').style.display = isPercentage ? 'none' : '';
    row.querySelector('.late-fee-percentage-group').style.display = isPercentage ? '' : 'none';
}

function onInstallmentTypeChange(select) {
    const row = select.closest('.installment-row');
    const isPercentage = select.value === 'percentage';

    row.querySelector('.installment-amount-group').style.display = isPercentage ? 'none' : '';
    row.querySelector('.installment-percentage-group').style.display = isPercentage ? '' : 'none';

    recalculateItems();
}

function recalculateInstallments(structureTotal) {
    let allocated = 0;

    document.querySelectorAll('#installmentRows .installment-row').forEach(function (row) {
        const isPercentage = row.querySelector('.installment-type').value === 'percentage';

        if (isPercentage) {
            const pct = parseFloat(row.querySelector('.installment-percentage').value || 0);
            allocated += structureTotal * pct / 100;
        } else {
            allocated += parseFloat(row.querySelector('.installment-amount').value || 0);
        }
    });

    const preview = document.getElementById('installmentAllocatedPreview');
    preview.innerText = '₹' + allocated.toFixed(2);
    preview.style.color = Math.abs(allocated - structureTotal) > 1 ? '#DC2626' : '#166534';
}

function removeRow(button, containerId) {
    const wrapper = document.getElementById(containerId);
    const selector = containerId === 'itemRows' ? '.item-row' : '.installment-row';
    const rows = wrapper.querySelectorAll(selector);

    if (rows.length <= 1 && containerId === 'itemRows') {
        return;
    }

    button.closest(selector).remove();
    recalculateItems();
}

function addItemRow() {
    document.getElementById('itemRows').appendChild(buildItemRow());
    recalculateItems();
}

function addInstallmentRow() {
    document.getElementById('installmentRows').appendChild(buildInstallmentRow());
    recalculateItems();
}

document.addEventListener('DOMContentLoaded', function () {
    const itemWrapper = document.getElementById('itemRows');
    const items = initialItems.length ? initialItems : [{}];
    items.forEach(item => itemWrapper.appendChild(buildItemRow(item)));

    const installmentWrapper = document.getElementById('installmentRows');
    initialInstallments.forEach(installment => installmentWrapper.appendChild(buildInstallmentRow(installment)));

    recalculateItems();

    const branchSelect = document.getElementById('branch_id');
    const courseSelect = document.getElementById('course_id');
    const batchSelect = document.getElementById('batch_id');
    const coursesByBranch = @json($coursesByBranch);
    const batchesByBranchCourse = @json($batchesByBranchCourse);

    cascadeByParent(courseSelect, branchSelect, coursesByBranch, {
        placeholder: @json(trans('global.pleaseSelect')),
        keepValue: @json(old('course_id', $isEdit ? $feeStructure->course_id : null)),
    });

    cascadeByBranchCourse(batchSelect, branchSelect, courseSelect, batchesByBranchCourse, {
        placeholder: 'All Batches / Optional',
        keepValue: @json(old('batch_id', $isEdit ? $feeStructure->batch_id : null)),
    });
});
</script>
