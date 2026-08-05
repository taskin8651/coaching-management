@extends('layouts.admin')
@section('content')
<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.homeworks.index') }}" class="admin-back-link">{{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">{{ $homework->title }}</h2>
    </div>
</div>

<div class="page-card mb-4">
    <div class="page-card-header">
        <p class="page-card-title">Attachments</p>
        <span class="page-card-note">
            <i class="fas fa-paperclip"></i>
            {{ count($homework->attachments) }} file(s)
        </span>
    </div>

    <div style="padding:20px;">
        @if($homework->attachments && count($homework->attachments))
            <div class="tag-wrap">
                @foreach($homework->attachments as $file)
                    <a href="{{ $file['url'] }}" target="_blank" class="role-tag">
                        <i class="fas fa-download"></i>
                        {{ $file['name'] }}
                    </a>
                @endforeach
            </div>
        @else
            <p class="field-hint" style="margin:0;">
                <i class="fas fa-info-circle"></i>
                No attachments uploaded for this homework.
            </p>
        @endif
    </div>
</div>

<div class="page-card">
    <div class="page-card-table">
        <table class="min-w-full datatable datatable-HomeworkSubs">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Status</th>
                    <th>Submitted At</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($homework->submissions as $sub)
                    <tr>
                        <td>{{ $sub->student->user->name ?? '-' }}</td>
                        <td>{{ ucfirst($sub->status) }}</td>
                        <td>{{ optional($sub->submitted_at)->format('d M Y h:i A') }}</td>
                        <td>{{ $sub->remarks }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
