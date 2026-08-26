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

    @include('admin.feePayments._form', ['feePayment' => $feePayment])

</form>

@endsection
