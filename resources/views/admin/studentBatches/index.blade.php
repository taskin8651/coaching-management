@extends('layouts.admin')

@section('page-title', 'Student Batches')

@section('content')
<div class="admin-page-head">
    <div><h2 class="admin-page-title">Student Batches</h2><p class="admin-page-subtitle">Multiple active batch assignments per student</p></div>
    @can('student_batch_create')<a href="{{ route('admin.student-batches.create') }}" class="btn-primary"><i class="fas fa-plus"></i> Assign Batch</a>@endcan
</div>
<div class="page-card"><div class="page-card-table"><table class="min-w-full datatable datatable-StudentBatches">
<thead><tr><th>ID</th><th>Student</th><th>Batch</th><th>Subject</th><th>Dates</th><th>Status</th><th style="text-align:right;">Actions</th></tr></thead>
<tbody>@foreach($studentBatches as $item)<tr>
<td>#{{ $item->id }}</td><td>{{ $item->student->user->name ?? $item->student->student_code ?? '-' }}</td><td>{{ $item->batch->name ?? '-' }}</td><td>{{ $item->subject->name ?? '-' }}</td>
<td>{{ $item->start_date ? $item->start_date->format('d M Y') : '-' }} to {{ $item->end_date ? $item->end_date->format('d M Y') : 'Active' }}</td>
<td><span class="status-pill {{ $item->status === 'active' ? 'success' : 'warning' }}">{{ ucfirst($item->status) }}</span></td>
<td style="text-align:right;"><div class="action-row">@can('student_batch_edit')<a class="btn-outline btn-outline-edit" href="{{ route('admin.student-batches.edit', $item->id) }}"><i class="fas fa-pencil-alt"></i> Edit</a>@endcan @can('student_batch_delete')<form method="POST" action="{{ route('admin.student-batches.destroy', $item->id) }}" style="display:inline" onsubmit="return confirm('{{ trans('global.areYouSure') }}')">@csrf @method('DELETE')<button class="btn-outline btn-outline-danger"><i class="fas fa-trash-alt"></i> Delete</button></form>@endcan</div></td>
</tr>@endforeach</tbody></table></div></div>
@endsection
@section('scripts')@parent<script>$(function(){initAdminDataTable('.datatable-StudentBatches',{searchPlaceholder:'Search student batches...'});});</script>@endsection
