@extends('layouts.admin')

@section('page-title', 'Edit Student Subject Assignments')

@section('content')
<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.student-batches.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">Edit Student Subject Assignments</h2>
        <p class="admin-page-subtitle">
            Managing: <strong>{{ $studentBatch->student->user->name ?? $studentBatch->student->student_code ?? ('Student #' . $studentBatch->student_id) }}</strong>
            — existing assignments checked rahenge; unchecked karne par remove ho jayenge.
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.student-batches.update', $studentBatch->id) }}" id="assignmentForm">
    @csrf
    @method('PUT')

    @include('admin.studentBatches.matrix', [
        'selectedBatchIds' => old('batch_ids', [$studentBatch->batch_id]),
        'selectedStatus' => old('status', $studentBatch->status),
        'oldAssignments' => old('assignments', []),
        'managedStudentId' => $managedStudentId,
    ])

    <div class="form-actions mt-4">
        <button type="submit" class="btn-primary">
            <i class="fas fa-save"></i>
            Update Assignments
        </button>
        <a href="{{ route('admin.student-batches.index') }}" class="btn-ghost">{{ trans('global.cancel') }}</a>
    </div>
</form>
@endsection
