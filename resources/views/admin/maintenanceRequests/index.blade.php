@extends('layouts.admin')
@section('page-title','Maintenance')
@section('content')
<div class="admin-page-head"><div><h2 class="admin-page-title">Maintenance</h2><p class="admin-page-subtitle">Issue reporting and repair tracking</p></div>@can('maintenance_create')<a href="{{ route('admin.maintenance-requests.create') }}" class="btn-primary"><i class="fas fa-plus"></i> Add Issue</a>@endcan</div>
<div class="page-card"><div class="page-card-table"><table class="min-w-full datatable datatable-Maintenance"><thead><tr><th>Title</th><th>Branch</th><th>Category</th><th>Priority</th><th>Status</th><th>Assigned</th><th>Actions</th></tr></thead><tbody>@foreach($maintenanceRequests as $item)<tr><td>{{ $item->title }}</td><td>{{ $item->branch->name ?? '-' }}</td><td>{{ $item->category }}</td><td>{{ ucfirst($item->priority) }}</td><td>{{ ucfirst(str_replace('_',' ',$item->status)) }}</td><td>{{ $item->assignedTo->name ?? '-' }}</td><td><a class="btn-outline" href="{{ route('admin.maintenance-requests.edit',$item->id) }}">Edit</a></td></tr>@endforeach</tbody></table></div></div>
@endsection
@section('scripts')@parent<script>$(function(){initAdminDataTable('.datatable-Maintenance',{searchPlaceholder:'Search maintenance...'});});</script>@endsection
