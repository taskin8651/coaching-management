@extends('layouts.admin')

@section('page-title', 'Assign Substitute Teacher')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.timetable-substitutions.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">Assign Substitute Teacher</h2>
        <p class="admin-page-subtitle">Create a substitute assignment for a scheduled timetable lecture.</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.timetable-substitutions.store') }}">
    @csrf

    @include('admin.timetableSubstitutions.partials.form', ['submitText' => 'Save Assignment'])
</form>

@endsection

