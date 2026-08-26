@php
    $isEdit = (bool) $event;
    $val = fn ($field, $default = null) => old($field, $isEdit ? $event->{$field} : $default);
    $selectedBatchIds = $isEdit ? $event->batches->pluck('id')->all() : (old('batch_ids') ?? []);
@endphp

<div class="admin-form-grid">

    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-icon"><i class="fas fa-calendar-alt"></i></div>
            <div>
                <p class="form-card-title">Event Information</p>
            </div>
        </div>

        <div class="form-card-body">
            <div class="field-group">
                <label class="field-label">Event Name <span class="req">*</span></label>
                <input type="text" name="name" value="{{ $val('name') }}" required class="field-input {{ $errors->has('name') ? 'error' : '' }}">
                @if($errors->has('name')) <p class="field-error">{{ $errors->first('name') }}</p> @endif
            </div>

            <div class="field-group">
                <label class="field-label">Event Code <span class="req">*</span></label>
                <input type="text" name="code" value="{{ $val('code') }}" required placeholder="Example: EVT-AI-2026" class="field-input {{ $errors->has('code') ? 'error' : '' }}">
                @if($errors->has('code')) <p class="field-error">{{ $errors->first('code') }}</p> @endif
            </div>

            <div class="field-group">
                <label class="field-label">Event Type</label>
                <input type="text" name="event_type" list="eventTypeSuggestions" value="{{ $val('event_type') }}" placeholder="Workshop, Trip, Seminar..." class="field-input">
                <datalist id="eventTypeSuggestions">
                    <option value="Workshop"><option value="Trip"><option value="Seminar">
                    <option value="Competition"><option value="Activity"><option value="MUN">
                </datalist>
            </div>

            <div class="field-group">
                <label class="field-label">Branch</label>
                <select name="branch_id" class="field-input">
                    @foreach($branches as $id => $branch)
                        <option value="{{ $id }}" {{ $val('branch_id') == $id ? 'selected' : '' }}>{{ $branch }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field-group">
                <label class="field-label">Venue</label>
                <input type="text" name="venue" value="{{ $val('venue') }}" class="field-input">
            </div>

            <div class="field-group" style="grid-column: 1 / -1;">
                <label class="field-label">Description</label>
                <textarea name="description" rows="3" class="field-input">{{ $val('description') }}</textarea>
            </div>
        </div>
    </div>

    <div class="form-card">
        <div class="form-card-header">
            <div class="form-card-icon"><i class="fas fa-calendar-alt"></i></div>
            <div>
                <p class="form-card-title">Dates & Registration Window</p>
            </div>
        </div>

        <div class="form-card-body">
            <div class="field-group">
                <label class="field-label">Start Date <span class="req">*</span></label>
                <input type="date" name="start_date" value="{{ $val('start_date') ? \Illuminate\Support\Carbon::parse($val('start_date'))->format('Y-m-d') : '' }}" required class="field-input {{ $errors->has('start_date') ? 'error' : '' }}">
            </div>
            <div class="field-group">
                <label class="field-label">End Date</label>
                <input type="date" name="end_date" value="{{ $val('end_date') ? \Illuminate\Support\Carbon::parse($val('end_date'))->format('Y-m-d') : '' }}" class="field-input {{ $errors->has('end_date') ? 'error' : '' }}">
            </div>
            <div class="field-group">
                <label class="field-label">Registration Start</label>
                <input type="date" name="registration_start_date" value="{{ $val('registration_start_date') ? \Illuminate\Support\Carbon::parse($val('registration_start_date'))->format('Y-m-d') : '' }}" class="field-input">
            </div>
            <div class="field-group">
                <label class="field-label">Registration End</label>
                <input type="date" name="registration_end_date" value="{{ $val('registration_end_date') ? \Illuminate\Support\Carbon::parse($val('registration_end_date'))->format('Y-m-d') : '' }}" class="field-input {{ $errors->has('registration_end_date') ? 'error' : '' }}">
            </div>

            <div class="field-group">
                <label class="field-label">Base Fee (₹)</label>
                <input type="number" step="0.01" min="0" name="base_fee" value="{{ $val('base_fee', 0) }}" class="field-input">
                <p class="field-hint">Fallback fee when no fee rule matches a participant. Configure rules on the event's detail page after creating it.</p>
            </div>

            <div class="field-group">
                <label class="field-label">Capacity</label>
                <input type="number" min="1" name="capacity" value="{{ $val('capacity') }}" class="field-input" placeholder="Leave blank for unlimited">
            </div>

            <div class="field-group">
                <label class="field-label">
                    <input type="checkbox" name="external_enrollment_allowed" value="1" {{ $val('external_enrollment_allowed', true) ? 'checked' : '' }}>
                    Allow External (non-Karmayoga) Participants
                </label>
            </div>
        </div>
    </div>

    <div class="form-card" style="grid-column: 1 / -1;">
        <div class="form-card-header between">
            <div class="form-card-head-left">
                <div class="form-card-icon"><i class="fas fa-users"></i></div>
                <div>
                    <p class="form-card-title">Eligible Batches</p>
                    <p class="form-card-subtitle">Which batches can enroll in bulk — leave empty to allow no batch-based bulk enrollment (individual/external enrollment still works)</p>
                </div>
            </div>

            <button type="button" class="btn-mini-primary" onclick="addBatchRow()">
                <i class="fas fa-plus"></i> Add Batch
            </button>
        </div>

        <div class="form-card-body">
            <div id="batchRows"></div>
        </div>
    </div>

</div>

<div class="form-actions">
    <button type="submit" class="btn-primary"><i class="fas fa-check"></i> {{ trans('global.save') }}</button>
    <a href="{{ route('admin.events.index') }}" class="btn-ghost">{{ trans('global.cancel') }}</a>
</div>

<style>
.batch-row {
    display: grid;
    grid-template-columns: 1fr 44px;
    gap: 14px;
    padding: 14px;
    border-radius: 18px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    margin-bottom: 12px;
    align-items: end;
}
.batch-remove {
    width: 44px; height: 44px; border: none; border-radius: 14px;
    background: #FEE2E2; color: #991B1B; cursor: pointer;
}
.batch-remove:hover { background: #EF4444; color: #fff; }
</style>

<script>
const batchesByBranch = @json($batchesByBranch);
const selectedBatchIds = @json($selectedBatchIds);

function batchOptions(selectedId) {
    let html = '';
    for (const branchId in batchesByBranch) {
        batchesByBranch[branchId].forEach(function (batch) {
            const sel = String(batch.id) === String(selectedId ?? '') ? 'selected' : '';
            html += `<option value="${batch.id}" ${sel}>${batch.name}</option>`;
        });
    }
    return html;
}

function buildBatchRow(batchId) {
    const row = document.createElement('div');
    row.className = 'batch-row';
    row.innerHTML = `
        <div class="field-group mb-0">
            <label class="field-label">Batch</label>
            <select name="batch_ids[]" class="field-input">
                ${batchOptions(batchId)}
            </select>
        </div>
        <button type="button" class="batch-remove" onclick="this.closest('.batch-row').remove()"><i class="fas fa-times"></i></button>
    `;
    return row;
}

function addBatchRow() {
    document.getElementById('batchRows').appendChild(buildBatchRow());
}

document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.getElementById('batchRows');
    (selectedBatchIds.length ? selectedBatchIds : []).forEach(function (id) {
        wrapper.appendChild(buildBatchRow(id));
    });
});
</script>
