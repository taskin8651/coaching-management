@extends('layouts.admin')

@section('page-title', 'Add Academic Year')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.academic-years.index') }}" class="admin-back-link">&larr; {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">Add Academic Year</h2>
        <p class="admin-page-subtitle">Create a reusable academic year for fee structures</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.academic-years.store') }}">
    @csrf

    @include('admin.academicYears.form', ['academicYear' => null])
</form>

@endsection
