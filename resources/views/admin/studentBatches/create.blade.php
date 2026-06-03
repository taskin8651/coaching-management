@extends('layouts.admin')
@section('page-title', 'Assign Student Batch')
@section('content')
@include('admin.studentBatches.form', ['action' => route('admin.student-batches.store'), 'method' => 'POST', 'studentBatch' => null])
@endsection
