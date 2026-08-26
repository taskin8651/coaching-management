@extends('layouts.admin')

@section('page-title', 'Edit Event')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.events.show', $event->id) }}" class="admin-back-link">← Back to Event</a>
        <h2 class="admin-page-title">Edit Event</h2>
        <p class="admin-page-subtitle">{{ $event->name }}</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.events.update', $event->id) }}">
    @csrf
    @method('PUT')
    @include('admin.events._form', ['event' => $event])
</form>

@endsection
