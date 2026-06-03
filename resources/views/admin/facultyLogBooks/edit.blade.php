@extends('layouts.admin')
@section('page-title', 'Edit Faculty Log')
@section('content')
@include('admin.facultyLogBooks.form', ['action' => route('admin.faculty-log-books.update', $facultyLogBook->id), 'method' => 'PUT'])
@endsection
