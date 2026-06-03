@extends('layouts.admin')
@section('page-title', 'Edit Extra Class')
@section('content')
@include('admin.extraClasses.form', ['action' => route('admin.extra-classes.update', $extraClass->id), 'method' => 'PUT'])
@endsection
