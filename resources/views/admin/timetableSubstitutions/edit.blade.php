@extends('layouts.admin')

@section('page-title', 'Edit Substitute Teacher')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.timetable-substitutions.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">Edit Substitute Teacher</h2>
        <p class="admin-page-subtitle">Update substitute teacher assignment details.</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.timetable-substitutions.update', $timetableSubstitution->id) }}">
    @method('PUT')
    @csrf

    @include('admin.timetableSubstitutions.partials.form', ['substitution' => $timetableSubstitution, 'submitText' => 'Update Assignment'])
</form>

@endsection

