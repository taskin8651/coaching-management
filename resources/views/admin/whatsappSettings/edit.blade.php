@extends('layouts.admin')

@section('page-title', 'Edit WhatsApp Setting')

@section('content')
@include('admin.whatsappSettings.form', ['action' => route('admin.whatsapp-settings.update', $whatsappSetting->id), 'method' => 'PUT', 'setting' => $whatsappSetting])
@endsection
