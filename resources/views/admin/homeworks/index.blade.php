@extends('layouts.admin')
@section('page-title','Homework')
@section('content')
<div class="admin-page-head"><div><h2 class="admin-page-title">Homework</h2><p class="admin-page-subtitle">Assignments and completion tracking</p></div>@can('homework_create')<a href="{{ route('admin.homeworks.create') }}" class="btn-primary"><i class="fas fa-plus"></i> Add Homework</a>@endcan</div>
<div class="page-card"><div class="page-card-table"><table class="min-w-full datatable datatable-Homeworks"><thead><tr><th>Title</th><th>Batch</th><th>Subject</th><th>Teacher</th><th>Due</th><th>Status</th><th>View</th></tr></thead><tbody>@foreach($homeworks as $item)<tr><td>{{ $item->title }}</td><td>{{ $item->batch->name ?? '-' }}</td><td>{{ $item->subject->name ?? '-' }}</td><td>{{ $item->teacher->user->name ?? '-' }}</td><td>{{ optional($item->due_date)->format('d M Y') }}</td><td>{{ ucfirst($item->status) }}</td><td><a class="btn-outline" href="{{ route('admin.homeworks.show',$item->id) }}">View</a></td></tr>@endforeach</tbody></table></div></div>
@endsection
@section('scripts')@parent<script>$(function(){initAdminDataTable('.datatable-Homeworks',{searchPlaceholder:'Search homework...'});});</script>@endsection
