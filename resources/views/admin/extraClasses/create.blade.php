@extends('layouts.admin')
@section('page-title', 'Add Extra Class')
@section('content')
@include('admin.extraClasses.form', ['action' => route('admin.extra-classes.store'), 'method' => 'POST', 'extraClass' => null])
@endsection
