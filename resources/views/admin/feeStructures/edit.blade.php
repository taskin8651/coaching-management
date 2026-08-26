@extends('layouts.admin')

@section('page-title', 'Edit Fee Structure')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.fee-structures.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Edit Fee Structure</h2>
        <p class="admin-page-subtitle">
            Update fee structure, line items and installment plan
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.fee-structures.update', $feeStructure->id) }}" id="feeStructureForm">
    @csrf
    @method('PUT')

    @include('admin.feeStructures._form', ['feeStructure' => $feeStructure, 'hasLedgers' => $hasLedgers])

</form>

@endsection
