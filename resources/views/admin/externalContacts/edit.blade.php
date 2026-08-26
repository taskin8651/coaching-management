@extends('layouts.admin')

@section('page-title', 'Edit External Contact')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.external-contacts.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">Edit External Contact</h2>
        <p class="admin-page-subtitle">{{ $externalContact->name }}</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.external-contacts.update', $externalContact->id) }}">
    @csrf
    @method('PUT')
    @include('admin.externalContacts._form', ['externalContact' => $externalContact])
</form>

@endsection
