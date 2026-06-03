@extends('layouts.admin')

@section('page-title', 'Add WhatsApp Setting')

@section('content')
@include('admin.whatsappSettings.form', ['action' => route('admin.whatsapp-settings.store'), 'method' => 'POST', 'setting' => null])
@endsection
