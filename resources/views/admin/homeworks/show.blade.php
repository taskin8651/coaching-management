@extends('layouts.admin')
@section('content')
<div class="admin-page-head"><div><a href="{{ route('admin.homeworks.index') }}" class="admin-back-link">{{ trans('global.back_to_list') }}</a><h2 class="admin-page-title">{{ $homework->title }}</h2></div></div><div class="page-card"><div class="page-card-table"><table class="min-w-full datatable datatable-HomeworkSubs"><thead><tr><th>Student</th><th>Status</th><th>Submitted At</th><th>Remarks</th></tr></thead><tbody>@foreach($homework->submissions as $sub)<tr><td>{{ $sub->student->user->name ?? '-' }}</td><td>{{ ucfirst($sub->status) }}</td><td>{{ optional($sub->submitted_at)->format('d M Y h:i A') }}</td><td>{{ $sub->remarks }}</td></tr>@endforeach</tbody></table></div></div>
@endsection
