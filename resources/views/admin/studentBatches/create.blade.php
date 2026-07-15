@extends('layouts.admin')

@section('page-title', 'Assign Student Subjects')

@section('content')
<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.student-batches.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">Assign Student Subjects</h2>
        <p class="admin-page-subtitle">Batches select karke matrix me students ko subjects assign karein.</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.student-batches.store') }}" id="assignmentForm">
    @csrf

    @include('admin.studentBatches.matrix', [
        'selectedBatchIds' => old('batch_ids', []),
        'selectedStatus' => old('status', 'active'),
        'oldAssignments' => old('assignments', []),
    ])

    <div class="form-actions mt-4">
        <button type="submit" class="btn-primary">
            <i class="fas fa-save"></i>
            Save Assignments
        </button>
        <a href="{{ route('admin.student-batches.index') }}" class="btn-ghost">{{ trans('global.cancel') }}</a>
    </div>
</form>
@endsection
