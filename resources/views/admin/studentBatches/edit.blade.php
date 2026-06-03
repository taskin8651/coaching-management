@extends('layouts.admin')
@section('page-title', 'Edit Student Batch')
@section('content')
@include('admin.studentBatches.form', ['action' => route('admin.student-batches.update', $studentBatch->id), 'method' => 'PUT'])
@endsection
