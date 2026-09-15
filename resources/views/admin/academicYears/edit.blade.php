@extends('layouts.admin')

@section('page-title', 'Edit Academic Year')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.academic-years.index') }}" class="admin-back-link">&larr; {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">Edit Academic Year</h2>
        <p class="admin-page-subtitle">Update academic year details</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.academic-years.update', $academicYear->id) }}">
    @csrf
    @method('PUT')

    @include('admin.academicYears.form', ['academicYear' => $academicYear])
</form>

@endsection
