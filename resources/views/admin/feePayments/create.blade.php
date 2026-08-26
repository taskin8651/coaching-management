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

    @include('admin.feePayments._form', ['feePayment' => null])

</form>

@endsection
