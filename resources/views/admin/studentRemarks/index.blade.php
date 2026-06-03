@extends('layouts.admin')
@section('page-title','Student Remarks')
@section('content')
<div class="admin-page-head"><div><h2 class="admin-page-title">Student Remarks</h2><p class="admin-page-subtitle">Positive and negative remark history</p></div>@can('student_remark_create')<a href="{{ route('admin.student-remarks.create') }}" class="btn-primary"><i class="fas fa-plus"></i> Add Remark</a>@endcan</div>
<div class="page-card"><div class="page-card-table"><table class="min-w-full datatable datatable-Remarks"><thead><tr><th>Date</th><th>Student</th><th>Type</th><th>Title</th><th>Remark</th><th>Parent</th></tr></thead><tbody>@foreach($remarks as $item)<tr><td>{{ optional($item->remark_date)->format('d M Y') }}</td><td>{{ $item->student->user->name ?? '-' }}</td><td>{{ ucfirst($item->remark_type) }}</td><td>{{ $item->title }}</td><td>{{ $item->remark }}</td><td>{{ $item->visible_to_parent ? 'Visible' : 'Hidden' }}</td></tr>@endforeach</tbody></table></div></div>
@endsection
@section('scripts')@parent<script>$(function(){initAdminDataTable('.datatable-Remarks',{searchPlaceholder:'Search remarks...'});});</script>@endsection
