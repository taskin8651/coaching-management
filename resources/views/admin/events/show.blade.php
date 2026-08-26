@extends('layouts.admin')

@section('page-title', 'Show Event')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.events.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">{{ $event->name }}</h2>
        <p class="admin-page-subtitle">{{ $event->code }} {{ $event->event_type ? '• ' . $event->event_type : '' }}</p>
    </div>

    <div class="show-actions">
        @can('event_edit')
            <a href="{{ route('admin.events.edit', $event->id) }}" class="btn-outline"><i class="fas fa-pencil-alt"></i> Edit</a>

            @if($event->status === 'draft' && in_array('open', $event->allowedTransitions()))
                <form action="{{ route('admin.events.publish', $event->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-primary"><i class="fas fa-bullhorn"></i> Publish</button>
                </form>
            @endif

            @if($event->status === 'closed' && in_array('open', $event->allowedTransitions()))
                <form action="{{ route('admin.events.reopen', $event->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-primary"><i class="fas fa-lock-open"></i> Reopen</button>
                </form>
            @endif

            @if($event->status === 'open' && in_array('closed', $event->allowedTransitions()))
                <form action="{{ route('admin.events.close', $event->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-outline"><i class="fas fa-lock"></i> Close</button>
                </form>
            @endif

            @if(in_array('cancelled', $event->allowedTransitions()))
                <form action="{{ route('admin.events.cancel', $event->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
                    @csrf
                    <button type="submit" class="btn-danger"><i class="fas fa-ban"></i> Cancel Event</button>
                </form>
            @endif
        @endcan
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card"><p class="stat-label">Total Enrolled</p><p class="stat-value">{{ $stats['total'] }}</p></div>
    <div class="stat-card"><p class="stat-label">Karmayoga Students</p><p class="stat-value">{{ $stats['students'] }}</p></div>
    <div class="stat-card"><p class="stat-label">External Participants</p><p class="stat-value">{{ $stats['external'] }}</p></div>
    <div class="stat-card"><p class="stat-label">Revenue Collected</p><p class="stat-value">₹{{ number_format($stats['revenue'], 0) }}</p></div>
    <div class="stat-card"><p class="stat-label">Paid / Partial / Unpaid</p><p class="stat-value" style="font-size:16px;">{{ $stats['paid'] }} / {{ $stats['partial'] }} / {{ $stats['unpaid'] }}</p></div>
    <div class="stat-card"><p class="stat-label">Complimentary</p><p class="stat-value">{{ $stats['complimentary'] }}</p></div>
    <div class="stat-card"><p class="stat-label">Attendance Marked</p><p class="stat-value">{{ $stats['attendance_marked'] }} ({{ $stats['present'] }} present)</p></div>
    <div class="stat-card"><p class="stat-label">Certificates Issued</p><p class="stat-value">{{ $stats['certificates_issued'] }}</p></div>
</div>

<div class="show-grid">
    <div>
        <div class="detail-card mb-3">
            <div class="profile-hero">
                <div class="profile-avatar-lg" style="background:#0EA5E9;"><i class="fas fa-calendar-alt"></i></div>
                <p class="profile-title">{{ $event->name }}</p>
                <p class="profile-subtitle">{{ $event->branch->name ?? 'Multi-Branch / HQ' }}</p>

                @if($event->status == 'open')
                    <span class="status-pill success">Open</span>
                @elseif($event->status == 'closed')
                    <span class="status-pill warning">Closed</span>
                @elseif($event->status == 'cancelled')
                    <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Cancelled</span>
                @else
                    <span class="status-pill">Draft</span>
                @endif
            </div>
        </div>

        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-info-circle"></i></div>
                <p class="detail-section-title">Basic Information</p>
            </div>
            <div class="detail-section-body">
                <div class="detail-row"><span class="detail-label">Venue</span><span class="detail-value">{{ $event->venue ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Dates</span><span class="detail-value">{{ optional($event->start_date)->format('d M Y') }}{{ $event->end_date ? ' — ' . $event->end_date->format('d M Y') : '' }}</span></div>
                <div class="detail-row"><span class="detail-label">Registration Window</span><span class="detail-value">{{ optional($event->registration_start_date)->format('d M Y') ?? '-' }} — {{ optional($event->registration_end_date)->format('d M Y') ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Base Fee</span><span class="detail-value">₹{{ number_format($event->base_fee, 2) }}</span></div>
                <div class="detail-row"><span class="detail-label">Capacity</span><span class="detail-value">{{ $event->capacity ?? 'Unlimited' }}</span></div>
                <div class="detail-row"><span class="detail-label">External Enrollment</span><span class="detail-value">{{ $event->external_enrollment_allowed ? 'Allowed' : 'Not Allowed' }}</span></div>
                <div class="detail-row"><span class="detail-label">Description</span><span class="detail-value">{{ $event->description ?? '-' }}</span></div>
            </div>
        </div>

        <div class="detail-card">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-users"></i></div>
                <p class="detail-section-title">Eligible Batches</p>
            </div>
            <div class="detail-section-body">
                @forelse($event->batches as $batch)
                    <div class="detail-row"><span class="detail-value">{{ $batch->name }}</span></div>
                @empty
                    <p class="table-sub-text">No batches configured — only individual/external enrollment applies.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div>
        {{-- FEE RULES --}}
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-rupee-sign"></i></div>
                <p class="detail-section-title">Fee Rules</p>
            </div>

            <div class="page-card-table">
                <table class="min-w-full">
                    <thead><tr><th>Type</th><th>Label</th><th>Amount</th><th>Condition</th><th>Status</th>@can('event_fee_rule_manage')<th></th>@endcan</tr></thead>
                    <tbody>
                        @forelse($event->feeRules as $rule)
                            <tr>
                                <td>{{ ucwords(str_replace('_', ' ', $rule->rule_type)) }}</td>
                                <td>{{ $rule->label ?? '-' }}</td>
                                <td>₹{{ number_format($rule->amount, 2) }}</td>
                                <td>
                                    @if($rule->rule_type == 'group') Min {{ $rule->min_group_size }} participants
                                    @elseif($rule->rule_type == 'early_bird') Until {{ optional($rule->valid_until)->format('d M Y') }}
                                    @else - @endif
                                </td>
                                <td>{{ ucfirst($rule->status) }}</td>
                                @can('event_fee_rule_manage')
                                <td>
                                    <form action="{{ route('admin.events.feeRules.destroy', [$event->id, $rule->id]) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
                                        @method('DELETE') @csrf
                                        <button type="submit" class="btn-outline btn-outline-danger" style="padding:4px 8px;"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </td>
                                @endcan
                            </tr>
                        @empty
                            <tr><td colspan="6">No fee rules — base fee (₹{{ number_format($event->base_fee, 2) }}) applies to everyone.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @can('event_fee_rule_manage')
            <div class="detail-section-body">
                <form method="POST" action="{{ route('admin.events.feeRules.store', $event->id) }}" id="feeRuleForm">
                    @csrf
                    <div class="field-group">
                        <label class="field-label">Rule Type</label>
                        <select name="rule_type" id="feeRuleType" class="field-input" onchange="toggleFeeRuleFields()">
                            <option value="karmayoga_student">Karmayoga Student</option>
                            <option value="external_student">External Student</option>
                            <option value="group">Group Fee (min size)</option>
                            <option value="early_bird">Early Bird (valid until)</option>
                        </select>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Label</label>
                        <input type="text" name="label" class="field-input" placeholder="Optional display label">
                    </div>
                    <div class="field-group">
                        <label class="field-label">Amount (₹)</label>
                        <input type="number" step="0.01" min="0" name="amount" required class="field-input">
                    </div>
                    <div class="field-group" id="minGroupSizeGroup" style="display:none;">
                        <label class="field-label">Min Group Size</label>
                        <input type="number" min="1" name="min_group_size" class="field-input">
                    </div>
                    <div class="field-group" id="validUntilGroup" style="display:none;">
                        <label class="field-label">Valid Until</label>
                        <input type="date" name="valid_until" class="field-input">
                    </div>
                    <button type="submit" class="btn-mini-primary"><i class="fas fa-plus"></i> Add Fee Rule</button>
                </form>
            </div>
            @endcan
        </div>

        {{-- BULK ENROLL --}}
        @can('event_enroll')
        @if($event->canEnroll())
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-user-plus"></i></div>
                <p class="detail-section-title">Bulk Enroll Students</p>
            </div>
            <div class="detail-section-body">
                @if($unenrolledStudents->isEmpty())
                    <p class="table-sub-text">No eligible students to enroll (configure eligible batches, or all eligible students are already enrolled).</p>
                @else
                    <form method="POST" action="{{ route('admin.events.bulkEnroll', $event->id) }}">
                        @csrf
                        <div class="field-group">
                            <label class="field-label">Select Students</label>
                            <select name="student_ids[]" multiple size="8" class="field-input">
                                @foreach($unenrolledStudents as $student)
                                    <option value="{{ $student->id }}">{{ $student->user->name ?? $student->student_code ?? 'Student #' . $student->id }}</option>
                                @endforeach
                            </select>
                            <p class="field-hint">Select 5+ students in one action to trigger a "group" fee rule, if configured.</p>
                        </div>
                        <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Enroll Selected Students</button>
                    </form>
                @endif
            </div>
        </div>

        {{-- REGISTER EXTERNAL --}}
        @if($event->external_enrollment_allowed)
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-user-friends"></i></div>
                <p class="detail-section-title">Register External Participant</p>
            </div>
            <div class="detail-section-body">
                <div class="field-group">
                    <label class="field-label">Search Existing Contact (mobile or name)</label>
                    <input type="text" id="contactSearch" class="field-input" placeholder="Type at least 3 characters...">
                    <div id="contactSearchResults" style="margin-top:8px;"></div>
                </div>

                <form method="POST" action="{{ route('admin.event-enrollments.store', $event->id) }}" id="externalEnrollForm">
                    @csrf
                    <input type="hidden" name="participant_type" value="external">
                    <input type="hidden" name="external_contact_id" id="selectedContactId">

                    <div id="newContactFields">
                        <div class="field-group">
                            <label class="field-label">Full Name <span class="req">*</span></label>
                            <input type="text" name="new_contact[name]" id="newContactName" class="field-input">
                        </div>
                        <div class="field-group">
                            <label class="field-label">Mobile <span class="req">*</span></label>
                            <input type="text" name="new_contact[mobile]" id="newContactMobile" class="field-input">
                        </div>
                        <div class="field-group">
                            <label class="field-label">School</label>
                            <input type="text" name="new_contact[school_name]" class="field-input">
                        </div>
                        <div class="field-group">
                            <label class="field-label">Standard</label>
                            <input type="text" name="new_contact[standard]" class="field-input">
                        </div>
                        <div class="field-group">
                            <label class="field-label">Guardian Name</label>
                            <input type="text" name="new_contact[guardian_name]" class="field-input">
                        </div>
                        <div class="field-group">
                            <label class="field-label">Guardian Mobile</label>
                            <input type="text" name="new_contact[guardian_mobile]" class="field-input">
                        </div>
                    </div>

                    <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Register Participant</button>
                </form>
            </div>
        </div>
        @endif
        @endif
        @endcan
    </div>
</div>

{{-- ENROLLMENTS TABLE --}}
<div class="page-card mt-3">
    <div class="page-card-header">
        <p class="page-card-title">Enrollments</p>
    </div>

    <div class="page-card-table">
        <table class="min-w-full">
            <thead>
                <tr>
                    <th>Participant</th>
                    <th>Type</th>
                    <th>Fee</th>
                    <th>Paid</th>
                    <th>Payment</th>
                    <th>Attendance</th>
                    <th>Certificate</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($event->enrollments as $enrollment)
                    <tr>
                        <td>
                            <p class="table-main-text">{{ $enrollment->participantName() }}</p>
                            @if($enrollment->fee_rule_label)
                                <p class="table-sub-text">{{ $enrollment->fee_rule_label }}</p>
                            @endif
                        </td>
                        <td>{{ $enrollment->participant_type === 'student' ? 'Karmayoga Student' : 'External' }}</td>
                        <td>₹{{ number_format($enrollment->fee_amount, 2) }}</td>
                        <td>₹{{ number_format($enrollment->paid_amount, 2) }}</td>
                        <td>
                            @if($enrollment->payment_status == 'paid')
                                <span class="status-pill success">Paid</span>
                            @elseif($enrollment->payment_status == 'partial')
                                <span class="status-pill warning">Partial</span>
                            @elseif($enrollment->payment_status == 'complimentary')
                                <span class="status-pill" style="background:#DBEAFE;color:#1D4ED8;">Complimentary</span>
                            @elseif($enrollment->payment_status == 'refunded')
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Refunded</span>
                            @else
                                <span class="status-pill">Unpaid</span>
                            @endif
                        </td>
                        <td>{{ is_null($enrollment->is_present) ? '-' : ($enrollment->is_present ? 'Present' : 'Absent') }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $enrollment->certificate_status)) }}</td>
                        <td>
                            @if($enrollment->status == 'cancelled')
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Cancelled</span>
                            @elseif($enrollment->status == 'waitlisted')
                                <span class="status-pill warning">Waitlisted</span>
                            @else
                                <span class="status-pill success">Registered</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-row">
                                @if($enrollment->status != 'cancelled')
                                    @can('event_payment_collect')
                                        @if($enrollment->payment_status != 'complimentary' && $enrollment->due_amount > 0)
                                            <button type="button" class="btn-outline" onclick="openCollectModal({{ $enrollment->id }}, {{ $enrollment->due_amount }})">
                                                <i class="fas fa-rupee-sign"></i> Collect
                                            </button>
                                        @endif
                                    @endcan

                                    @can('event_attendance_mark')
                                        <form method="POST" action="{{ route('admin.event-enrollments.attendance', $enrollment->id) }}" style="display:inline;">
                                            @csrf
                                            <input type="hidden" name="present" value="1">
                                            <button type="submit" class="btn-outline" style="color:#166534;"><i class="fas fa-check"></i> Present</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.event-enrollments.attendance', $enrollment->id) }}" style="display:inline;">
                                            @csrf
                                            <input type="hidden" name="present" value="0">
                                            <button type="submit" class="btn-outline btn-outline-danger"><i class="fas fa-times"></i> Absent</button>
                                        </form>
                                    @endcan

                                    @can('event_certificate_mark')
                                        @if($enrollment->certificate_status != 'issued')
                                            <form method="POST" action="{{ route('admin.event-enrollments.certificate', $enrollment->id) }}" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn-outline"><i class="fas fa-certificate"></i> Issue Cert</button>
                                            </form>
                                        @endif
                                    @endcan

                                    @can('event_fee_rule_manage')
                                        @if($enrollment->payment_status == 'unpaid')
                                            <form method="POST" action="{{ route('admin.event-enrollments.complimentary', $enrollment->id) }}" style="display:inline;" onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
                                                @csrf
                                                <button type="submit" class="btn-outline">Mark Free</button>
                                            </form>
                                        @endif
                                    @endcan

                                    @can('event_enroll')
                                        <form method="POST" action="{{ route('admin.event-enrollments.cancel', $enrollment->id) }}" style="display:inline;" onsubmit="return promptCancel(this)">
                                            @csrf
                                            <input type="hidden" name="cancel_reason" class="cancel-reason-input">
                                            <button type="submit" class="btn-outline btn-outline-danger"><i class="fas fa-ban"></i> Cancel</button>
                                        </form>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9">No enrollments yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- COLLECT PAYMENT MODAL --}}
@can('event_payment_collect')
<div id="collectPaymentModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:1000;align-items:center;justify-content:center;">
    <div class="detail-card" style="max-width:480px;width:90%;max-height:90vh;overflow-y:auto;">
        <div class="detail-section-head">
            <div class="detail-section-icon"><i class="fas fa-rupee-sign"></i></div>
            <p class="detail-section-title">Collect Payment</p>
        </div>

        <form method="POST" id="collectPaymentForm" class="detail-section-body">
            @csrf
            <div class="field-group">
                <label class="field-label">Fee Account <span class="req">*</span></label>
                <select name="fee_account_id" required class="field-input">
                    @foreach($feeAccounts as $id => $account)
                        <option value="{{ $id }}">{{ $account }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field-group">
                <label class="field-label">Amount <span class="req">*</span></label>
                <input type="number" step="0.01" min="0.01" name="paid_amount" id="collectAmount" required class="field-input">
            </div>
            <div class="field-group">
                <label class="field-label">Payment Mode <span class="req">*</span></label>
                <select name="payment_mode" id="collectMode" required class="field-input" onchange="toggleCollectModeFields()">
                    <option value="cash">Cash</option>
                    <option value="upi">UPI</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="cheque">Cheque</option>
                    <option value="card">Card</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="field-group collect-mode-field collect-mode-cheque" style="display:none;">
                <label class="field-label">Cheque Number</label>
                <input type="text" name="cheque_number" class="field-input">
            </div>
            <div class="field-group collect-mode-field collect-mode-cheque" style="display:none;">
                <label class="field-label">Cheque Date</label>
                <input type="date" name="cheque_date" class="field-input">
            </div>
            <div class="field-group collect-mode-field collect-mode-upi" style="display:none;">
                <label class="field-label">UPI Transaction Ref</label>
                <input type="text" name="upi_txn_ref" class="field-input">
            </div>
            <div class="field-group collect-mode-field collect-mode-bank_transfer" style="display:none;">
                <label class="field-label">UTR</label>
                <input type="text" name="neft_rtgs_imps_utr" class="field-input">
            </div>
            <div class="field-group collect-mode-field collect-mode-card" style="display:none;">
                <label class="field-label">Card / Gateway Ref</label>
                <input type="text" name="card_gateway_ref" class="field-input">
            </div>
            <div class="field-group collect-mode-field collect-mode-other" style="display:none;">
                <label class="field-label">Reference</label>
                <input type="text" name="other_reference" class="field-input">
            </div>

            <div class="form-actions" style="margin-top:12px;">
                <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Collect Payment</button>
                <button type="button" class="btn-ghost" onclick="document.getElementById('collectPaymentModal').style.display='none'">Close</button>
            </div>
        </form>
    </div>
</div>
@endcan

<script>
function openCollectModal(enrollmentId, dueAmount) {
    document.getElementById('collectPaymentForm').action = '/admin/event-enrollments/' + enrollmentId + '/collect-payment';
    document.getElementById('collectAmount').value = dueAmount;
    document.getElementById('collectPaymentModal').style.display = 'flex';
    toggleCollectModeFields();
}

function toggleCollectModeFields() {
    const mode = document.getElementById('collectMode').value;
    document.querySelectorAll('.collect-mode-field').forEach(function (el) {
        el.style.display = el.classList.contains('collect-mode-' + mode) ? '' : 'none';
    });
}

function promptCancel(form) {
    const reason = prompt('Reason for cancelling this enrollment:');
    if (!reason) return false;
    form.querySelector('.cancel-reason-input').value = reason;
    return confirm('{{ trans("global.areYouSure") }}');
}

function toggleFeeRuleFields() {
    const type = document.getElementById('feeRuleType').value;
    document.getElementById('minGroupSizeGroup').style.display = type === 'group' ? '' : 'none';
    document.getElementById('validUntilGroup').style.display = type === 'early_bird' ? '' : 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    const feeRuleType = document.getElementById('feeRuleType');
    if (feeRuleType) {
        toggleFeeRuleFields();
    }

    const searchInput = document.getElementById('contactSearch');
    if (searchInput) {
        let timeout;
        searchInput.addEventListener('input', function () {
            clearTimeout(timeout);
            const q = this.value;
            const resultsBox = document.getElementById('contactSearchResults');

            if (q.length < 3) {
                resultsBox.innerHTML = '';
                return;
            }

            timeout = setTimeout(function () {
                fetch('{{ route('admin.external-contacts.search') }}?q=' + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(function (contacts) {
                        if (!contacts.length) {
                            resultsBox.innerHTML = '<p class="table-sub-text">No matching contact — fill the form below to create a new one.</p>';
                            return;
                        }

                        resultsBox.innerHTML = contacts.map(function (c) {
                            return `<div class="quick-link" style="cursor:pointer;" onclick="selectContact(${c.id}, '${c.name.replace(/'/g, "")}', '${c.mobile}')">
                                <i class="fas fa-user"></i> ${c.name} — ${c.mobile} ${c.school_name ? '(' + c.school_name + ')' : ''}
                            </div>`;
                        }).join('');
                    });
            }, 300);
        });
    }
});

function selectContact(id, name, mobile) {
    document.getElementById('selectedContactId').value = id;
    document.getElementById('newContactFields').style.display = 'none';
    document.getElementById('contactSearchResults').innerHTML = '<p class="table-sub-text">Selected: ' + name + ' (' + mobile + ') — <a href="#" onclick="clearContactSelection(); return false;">change</a></p>';
}

function clearContactSelection() {
    document.getElementById('selectedContactId').value = '';
    document.getElementById('newContactFields').style.display = '';
    document.getElementById('contactSearchResults').innerHTML = '';
}
</script>

@endsection
