@extends('layouts.admin')

@section('page-title', 'Add Fee Structure')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.fee-structures.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Add Fee Structure</h2>
        <p class="admin-page-subtitle">
            Define branch, course and batch wise fee structure with fee-head line items and installments
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.fee-structures.store') }}" id="feeStructureForm">
    @csrf

    @include('admin.feeStructures._form', ['feeStructure' => null])

</form>

@endsection
