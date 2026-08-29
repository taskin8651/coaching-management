@extends('layouts.admin')

@section('page-title', 'Edit Holiday')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.holidays.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">Edit Holiday</h2>
        <p class="admin-page-subtitle">{{ $holiday->name }}</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.holidays.update', $holiday->id) }}">
    @csrf
    @method('PUT')
    @include('admin.holidays._form', ['holiday' => $holiday])
</form>

@endsection
