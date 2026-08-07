@extends('layouts.admin')

@section('page-title', 'Edit Expense')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.expenses.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Edit Expense</h2>

        <p class="admin-page-subtitle">
            Update branch-wise expense and payment details
        </p>
    </div>

    <div class="identity-card">
        <div class="identity-avatar" style="background:#EF4444;">
            <i class="fas fa-file-invoice-dollar"></i>
        </div>

        <div>
            <p class="identity-title">{{ $expense->title }}</p>
            <p class="identity-subtitle">ID #{{ $expense->id }}</p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.expenses.update', $expense->id) }}">
    @method('PUT')
    @csrf

    <div class="admin-form-grid">

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>

                <div>
                    <p class="form-card-title">Expense Details</p>
                    <p class="form-card-subtitle">Update expense information</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="title">
                        Expense Title <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-receipt icon"></i>

                        <input type="text"
                               name="title"
                               id="title"
                               value="{{ old('title', $expense->title) }}"
                               required
                               class="field-input {{ $errors->has('title') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('title'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('title') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="category">Category</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-tags icon"></i>

                        <select name="category"
                                id="category"
                                class="field-input {{ $errors->has('category') ? 'error' : '' }}">
                            <option value="">Please select</option>
                            @foreach($categories as $key => $category)
                                <option value="{{ $key }}" {{ old('category', $expense->category) == $key ? 'selected' : '' }}>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($errors->has('category'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('category') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="branch_id">Branch</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-school icon"></i>

                        <select name="branch_id"
                                id="branch_id"
                                class="field-input {{ $errors->has('branch_id') ? 'error' : '' }}">
                            @foreach($branches as $id => $branch)
                                <option value="{{ $id }}" {{ old('branch_id', $expense->branch_id) == $id ? 'selected' : '' }}>
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
                               value="{{ old('amount', $expense->amount) }}"
                               required
                               class="field-input {{ $errors->has('amount') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('amount'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('amount') }}</p>
                    @endif
                </div>

            </div>
        </div>

        <div class="form-card">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-credit-card"></i>
                </div>

                <div>
                    <p class="form-card-title">Payment Information</p>
                    <p class="form-card-subtitle">Update payment mode, date and paid by</p>
                </div>
            </div>

            <div class="form-card-body">

                <div class="field-group">
                    <label class="field-label" for="expense_date">Expense Date</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-calendar icon"></i>

                        <input type="date"
                               name="expense_date"
                               id="expense_date"
                               value="{{ old('expense_date', $expense->expense_date ? \Carbon\Carbon::parse($expense->expense_date)->format('Y-m-d') : '') }}"
                               class="field-input {{ $errors->has('expense_date') ? 'error' : '' }}">
                    </div>

                    @if($errors->has('expense_date'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('expense_date') }}</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="payment_mode">
                        Payment Mode <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-wallet icon"></i>

                        <select name="payment_mode"
                                id="payment_mode"
                                required
                                class="field-input {{ $errors->has('payment_mode') ? 'error' : '' }}">
                            @foreach($paymentModes as $key => $mode)
                                <option value="{{ $key }}" {{ old('payment_mode', $expense->payment_mode) == $key ? 'selected' : '' }}>
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
                    <label class="field-label" for="paid_by_id">Paid By</label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-user-shield icon"></i>

                        <select name="paid_by_id"
                                id="paid_by_id"
                                class="field-input {{ $errors->has('paid_by_id') ? 'error' : '' }}">
                            <option value="">{{ trans('global.pleaseSelect') }}</option>
                        </select>
                    </div>

                    @if($errors->has('paid_by_id'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('paid_by_id') }}</p>
                    @else
                        <p class="field-hint">Select a branch first — only Admin, Branch Manager and Staff of that branch can be picked.</p>
                    @endif
                </div>

                <div class="field-group">
                    <label class="field-label" for="status">
                        Status <span class="req">*</span>
                    </label>

                    <div class="input-icon-wrap">
                        <i class="fas fa-toggle-on icon"></i>

                        <select name="status"
                                id="status"
                                required
                                class="field-input {{ $errors->has('status') ? 'error' : '' }}">
                            <option value="paid" {{ old('status', $expense->status) == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="pending" {{ old('status', $expense->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="cancelled" {{ old('status', $expense->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    @if($errors->has('status'))
                        <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('status') }}</p>
                    @endif
                </div>

            </div>
        </div>

        <div class="form-card" style="grid-column: 1 / -1;">
            <div class="form-card-header">
                <div class="form-card-icon">
                    <i class="fas fa-file-alt"></i>
                </div>

                <div>
                    <p class="form-card-title">Bill & Remarks</p>
                    <p class="form-card-subtitle">Update vendor, bill number and notes</p>
                </div>
            </div>

            <div class="form-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="field-group">
                            <label class="field-label" for="vendor_name">Vendor / Person Name</label>

                            <div class="input-icon-wrap">
                                <i class="fas fa-user icon"></i>

                                <input type="text"
                                       name="vendor_name"
                                       id="vendor_name"
                                       value="{{ old('vendor_name', $expense->vendor_name) }}"
                                       class="field-input {{ $errors->has('vendor_name') ? 'error' : '' }}">
                            </div>

                            @if($errors->has('vendor_name'))
                                <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('vendor_name') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="field-group">
                            <label class="field-label" for="bill_no">Bill / Invoice No</label>

                            <div class="input-icon-wrap">
                                <i class="fas fa-hashtag icon"></i>

                                <input type="text"
                                       name="bill_no"
                                       id="bill_no"
                                       value="{{ old('bill_no', $expense->bill_no) }}"
                                       class="field-input {{ $errors->has('bill_no') ? 'error' : '' }}">
                            </div>

                            @if($errors->has('bill_no'))
                                <p class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('bill_no') }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="remarks">Remarks</label>

                    <textarea name="remarks"
                              id="remarks"
                              rows="5"
                              class="field-input {{ $errors->has('remarks') ? 'error' : '' }}">{{ old('remarks', $expense->remarks) }}</textarea>

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

        <a href="{{ route('admin.expenses.index') }}" class="btn-ghost">
            {{ trans('global.cancel') }}
        </a>
    </div>

</form>

@section('scripts')
@parent
<script>
document.addEventListener('DOMContentLoaded', function () {
    const branchSelect = document.getElementById('branch_id');
    const paidBySelect = document.getElementById('paid_by_id');
    const usersByBranch = @json($usersByBranch);

    cascadeByParent(paidBySelect, branchSelect, usersByBranch, {
        placeholder: @json(trans('global.pleaseSelect')),
        keepValue: @json(old('paid_by_id', $expense->paid_by_id)),
    });
});
</script>
@endsection

@endsection