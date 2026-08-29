@extends('layouts.admin')

@section('page-title', 'Add Holiday')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.holidays.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">Add Holiday</h2>
        <p class="admin-page-subtitle">Add a holiday to the calendar — used in salary working-days calculation</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.holidays.store') }}">
    @csrf
    @include('admin.holidays._form', ['holiday' => null])
</form>

@endsection
