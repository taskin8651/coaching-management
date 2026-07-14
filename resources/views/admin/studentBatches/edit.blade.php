@extends('layouts.admin')

@section('page-title', 'Edit Student Subject Assignments')

@section('styles')
<style>
    .assignment-toolbar{display:flex;flex-wrap:wrap;gap:12px;align-items:end;justify-content:space-between;margin-bottom:16px}
    .assignment-toolbar .field-group{margin-bottom:0;min-width:260px}
    .matrix-stats{display:flex;gap:10px;flex-wrap:wrap}
    .matrix-stat{border:1px solid #dbeafe;background:#eff6ff;color:#1d4ed8;border-radius:12px;padding:9px 13px;font-size:13px;font-weight:800}
    .matrix-card{border:1px solid #e2e8f0;border-radius:18px;background:#fff;box-shadow:0 14px 36px rgba(15,23,42,.05);overflow:hidden}
    .matrix-loader{display:none;padding:28px;text-align:center;color:#475569;font-weight:700}
    .matrix-loader.active{display:block}
    .matrix-scroll{max-height:68vh;overflow:auto}
    .assignment-matrix{margin:0;min-width:900px;border-collapse:separate;border-spacing:0}
    .assignment-matrix thead th{position:sticky;top:0;z-index:4;background:#0f3b73;color:#fff;text-align:center;vertical-align:middle;font-size:13px;border-color:#174b8d!important;white-space:nowrap}
    .assignment-matrix tbody tr:hover td{background:#f8fbff}
    .assignment-matrix th,.assignment-matrix td{padding:12px!important;border-color:#e5edf7!important}
    .assignment-matrix .student-col{position:sticky;left:0;z-index:5;background:#fff;text-align:left;min-width:240px;box-shadow:8px 0 16px rgba(15,23,42,.04)}
    .assignment-matrix thead .student-col{background:#0b2f5f;color:#fff;z-index:8}
    .student-name{font-weight:900;color:#0f172a}.student-code{font-size:12px;color:#64748b;margin-top:2px}
    .matrix-check{width:18px;height:18px;accent-color:#0f5ed7;cursor:pointer}
    .subject-head{display:flex;flex-direction:column;align-items:center;gap:6px;min-width:116px}
    .subject-head span{font-weight:900}.subject-head small{font-size:10px;font-weight:700;color:#dbeafe}
    .empty-matrix{padding:34px;text-align:center;color:#64748b}
    .pagination-wrap{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:14px 16px;border-top:1px solid #e2e8f0;background:#fbfdff}
    .pagination-wrap .btn{border-radius:10px;font-weight:800}
    .page-info{font-size:13px;color:#475569;font-weight:700}
</style>
@endsection

@section('content')
<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.student-batches.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">Edit Student Subject Assignments</h2>
        <p class="admin-page-subtitle">Existing assignments checked rahenge; unchecked karne par remove ho jayenge.</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.student-batches.update', $studentBatch->id) }}" id="assignmentForm">
    @csrf
    @method('PUT')

    @include('admin.studentBatches.matrix', [
        'selectedBatchId' => old('batch_id', $studentBatch->batch_id),
        'selectedStatus' => old('status', $studentBatch->status),
        'oldAssignments' => old('assignments', []),
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
