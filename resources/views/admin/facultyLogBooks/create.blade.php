@extends('layouts.admin')
@section('page-title', 'Add Faculty Log')
@section('content')
@include('admin.facultyLogBooks.form', ['action' => route('admin.faculty-log-books.store'), 'method' => 'POST', 'facultyLogBook' => null])
@endsection
