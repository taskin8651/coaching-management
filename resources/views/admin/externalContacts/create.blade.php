@extends('layouts.admin')

@section('page-title', 'Add External Contact')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.external-contacts.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">Add External Contact</h2>
        <p class="admin-page-subtitle">Register a non-Karmayoga participant for future event communication</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.external-contacts.store') }}">
    @csrf
    @include('admin.externalContacts._form', ['externalContact' => null])
</form>

@endsection
