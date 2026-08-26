@extends('layouts.admin')

@section('page-title', 'Add Event')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.events.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">Add Event</h2>
        <p class="admin-page-subtitle">Create a new workshop, trip, seminar or activity</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.events.store') }}">
    @csrf
    @include('admin.events._form', ['event' => null])
</form>

@endsection
