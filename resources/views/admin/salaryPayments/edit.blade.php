@extends('layouts.admin')

@section('page-title', 'Edit Salary Payment')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.salary-payments.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Edit Salary Payment</h2>

        <p class="admin-page-subtitle">
            Update salary payment and salary slip details
        </p>
    </div>

    <div class="identity-card">
        <div class="identity-avatar" style="background:#10B981;">
            <i class="fas fa-money-check-alt"></i>
        </div>

        <div>
            <p class="identity-title">{{ $salaryPayment->slip_no ?? 'Salary Slip' }}</p>
            <p class="identity-subtitle">ID #{{ $salaryPayment->id }}</p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.salary-payments.update', $salaryPayment->id) }}">
    @method('PUT')
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-user-tie"></i>
                </div>

                <div>
                    <p class="form-card-title">Employee Details</p>
                    <p class="form-card-subtitle">Update employee type and employee</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="employee_type">Employee Type <span class="req">*</span></label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-users icon"></i>

                        <select name="employee_type" id="employee_type" required class="field-input {{ $errors->has('employee_type') ? 'error' : '' }}">
                            <option value="teacher" {{ old('employee_type', $salaryPayment->employee_type) == 'teacher' ? 'selected' : '' }}>Teacher</option>
                            <option value="staff" {{ old('employee_type', $salaryPayment->employee_type) == 'staff' ? 'selected' : '' }}>Staff</option>
                            <option value="manager" {{ old('employee_type', $salaryPayment->employee_type) == 'manager' ? 'selected' : '' }}>Manager</option>
                        </select>
                    </div>

                    @if($errors->has('employee_type'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('employee_type') }}</p>
                    @endif
                </div>

                <div class="field-group" id="teacherBox">
                    <label class="field-label" for="teacher_id">Teacher</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-chalkboard-teacher icon"></i>

                        <select name="teacher_id" id="teacher_id" class="field-input {{ $errors->has('teacher_id') ? 'error' : '' }}">
                            @foreach($teachers as $id => $teacher)
                                <option value="{{ $id }}" {{ old('teacher_id', $salaryPayment->teacher_id) == $id ? 'selected' : '' }}>
                                    {{ $teacher }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('teacher_id'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('teacher_id') }}</p>
                    @endif
                </div>

                <div class="field-group" id="staffBox">
                    <label class="field-label" for="staff_id">Staff / Manager</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-user-tie icon"></i>

                        <select name="staff_id" id="staff_id" class="field-input {{ $errors->has('staff_id') ? 'error' : '' }}">
                            @foreach($staff as $id => $member)
                                <option value="{{ $id }}" {{ old('staff_id', $salaryPayment->staff_id) == $id ? 'selected' : '' }}>
                                    {{ $member }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('staff_id'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('staff_id') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="branch_id">Branch</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-school icon"></i>

                        <select name="branch_id" id="branch_id" class="field-input {{ $errors->has('branch_id') ? 'error' : '' }}">
                            @foreach($branches as $id => $branch)
                                <option value="{{ $id }}" {{ old('branch_id', $salaryPayment->branch_id) == $id ? 'selected' : '' }}>
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
                    <label class="field-label" for="salary_month">Salary Month <span class="req">*</span></label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar icon"></i>

                        <input type="month"
                               name="salary_month"
                               id="salary_month"
                               value="{{ old('salary_month', $salaryPayment->salary_month) }}"
                               required
                               class="field-input {{ $errors->has('salary_month') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('salary_month'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('salary_month') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="slip_no">Slip No</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-receipt icon"></i>

                        <input type="text"
                               name="slip_no"
                               id="slip_no"
                               value="{{ old('slip_no', $salaryPayment->slip_no) }}"
                               class="field-input {{ $errors->has('slip_no') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('slip_no'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('slip_no') }}</p>
                    @endif
                </div>

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-money-check-alt"></i>
                </div>

                <div>
                    <p class="form-card-title">Salary Details</p>
                    <p class="form-card-subtitle">Update amount details</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="basic_salary">Basic Salary <span class="req">*</span></label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-rupee-sign icon"></i>

                        <input type="number"
                               step="0.01"
                               min="0"
                               name="basic_salary"
                               id="basic_salary"
                               value="{{ old('basic_salary', $salaryPayment->basic_salary) }}"
                               required
                               class="field-input {{ $errors->has('basic_salary') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('basic_salary'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('basic_salary') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="bonus">Bonus</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-plus-circle icon"></i>

                        <input type="number"
                               step="0.01"
                               min="0"
                               name="bonus"
                               id="bonus"
                               value="{{ old('bonus', $salaryPayment->bonus) }}"
                               class="field-input {{ $errors->has('bonus') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('bonus'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('bonus') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="deduction">Deduction</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-minus-circle icon"></i>

                        <input type="number"
                               step="0.01"
                               min="0"
                               name="deduction"
                               id="deduction"
                               value="{{ old('deduction', $salaryPayment->deduction) }}"
                               class="field-input {{ $errors->has('deduction') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('deduction'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('deduction') }}</p>
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
                               value="{{ old('paid_amount', $salaryPayment->paid_amount) }}"
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
                                <option value="{{ $key }}" {{ old('payment_mode', $salaryPayment->payment_mode) == $key ? 'selected' : '' }}>
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
                            <option value="paid" {{ old('payment_status', $salaryPayment->payment_status) == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="partial" {{ old('payment_status', $salaryPayment->payment_status) == 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="due" {{ old('payment_status', $salaryPayment->payment_status) == 'due' ? 'selected' : '' }}>Due</option>
                            <option value="cancelled" {{ old('payment_status', $salaryPayment->payment_status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    @if($errors->has('payment_status'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('payment_status') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="payment_date">Payment Date</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar-check icon"></i>

                        <input type="date"
                               name="payment_date"
                               id="payment_date"
                               value="{{ old('payment_date', $salaryPayment->payment_date ? \Carbon\Carbon::parse($salaryPayment->payment_date)->format('Y-m-d') : '') }}"
                               class="field-input {{ $errors->has('payment_date') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('payment_date'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('payment_date') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="paid_by_id">Paid By</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-user-shield icon"></i>

                        <select name="paid_by_id" id="paid_by_id" class="field-input {{ $errors->has('paid_by_id') ? 'error' : '' }}">
                            @foreach($users as $id => $user)
                                <option value="{{ $id }}" {{ old('paid_by_id', $salaryPayment->paid_by_id) == $id ? 'selected' : '' }}>
                                    {{ $user }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('paid_by_id'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('paid_by_id') }}</p>
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
                    <p class="form-card-subtitle">Final calculation backend se auto hoga.</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="stats-grid">
                    <div class="stat-card">
                        <p class="stat-label">Net Salary</p>
                        <p class="stat-value" id="netPreview">₹0</p>
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
                              class="field-input {{ $errors->has('remarks') ? 'error' : '' }}">{{ old('remarks', $salaryPayment->remarks) }}</textarea>

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

        <a href="{{ route('admin.salary-payments.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</form>

@endsection

@section('scripts')
@parent
<script>
function toggleEmployeeBox() {
    const type = document.getElementById('employee_type').value;
    document.getElementById('teacherBox').style.display = type === 'teacher' ? 'block' : 'none';
    document.getElementById('staffBox').style.display = type === 'teacher' ? 'none' : 'block';
}

function updateSalaryPreview() {
    const basic = parseFloat(document.getElementById('basic_salary').value || 0);
    const bonus = parseFloat(document.getElementById('bonus').value || 0);
    const deduction = parseFloat(document.getElementById('deduction').value || 0);
    const paid = parseFloat(document.getElementById('paid_amount').value || 0);

    const net = Math.max((basic + bonus) - deduction, 0);
    const due = Math.max(net - paid, 0);

    document.getElementById('netPreview').innerText = '₹' + net.toFixed(2);
    document.getElementById('duePreview').innerText = '₹' + due.toFixed(2);
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('employee_type').addEventListener('change', toggleEmployeeBox);

    ['basic_salary', 'bonus', 'deduction', 'paid_amount'].forEach(function (id) {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', updateSalaryPreview);
        }
    });

    toggleEmployeeBox();
    updateSalaryPreview();
});
</script>
@endsection