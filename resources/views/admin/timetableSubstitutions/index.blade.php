@extends('layouts.admin')

@section('page-title', 'Substitute Teachers')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Substitute Teachers</h2>
        <p class="admin-page-subtitle">
            Track and manage substitute teacher assignments for timetable lectures.
        </p>
    </div>

    @can('timetable_substitute')
        <a href="{{ route('admin.timetable-substitutions.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Assign Substitute
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Assignments</p>
        <p class="stat-value">{{ $substitutions->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Today</p>
        <p class="stat-value">
            {{ $substitutions->filter(fn($item) => $item->substitution_date && \Carbon\Carbon::parse($item->substitution_date)->isToday())->count() }}
        </p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Upcoming</p>
        <p class="stat-value">
            {{ $substitutions->filter(fn($item) => $item->substitution_date && \Carbon\Carbon::parse($item->substitution_date)->isFuture())->count() }}
        </p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Past</p>
        <p class="stat-value">
            {{ $substitutions->filter(fn($item) => $item->substitution_date && \Carbon\Carbon::parse($item->substitution_date)->isPast() && ! \Carbon\Carbon::parse($item->substitution_date)->isToday())->count() }}
        </p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Substitute Assignments</p>

        <span class="page-card-note">
            <i class="fas fa-user-clock"></i>
            Timetable-wise substitute history
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-Substitutions">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Batch / Subject</th>
                    <th>Original Teacher</th>
                    <th>Substitute Teacher</th>
                    <th>Changed By</th>
                    <th>Reason</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>

            <tbody>
                @foreach($substitutions as $item)
                    <tr data-entry-id="{{ $item->id }}">
                        <td>
                            <p class="table-main-text">
                                {{ $item->substitution_date ? \Carbon\Carbon::parse($item->substitution_date)->format('d M Y') : '-' }}
                            </p>
                            <p class="table-sub-text">
                                {{ $item->substitution_date ? \Carbon\Carbon::parse($item->substitution_date)->format('l') : '-' }}
                            </p>
                        </td>

                        <td>
                            <p class="table-main-text">
                                {{ $item->timetable->batch->name ?? '-' }}
                            </p>

                            <p class="table-sub-text">
                                {{ $item->timetable->subject->name ?? '-' }}

                                @if($item->timetable)
                                    · {{ $item->timetable->start_time ?? '-' }} - {{ $item->timetable->end_time ?? '-' }}
                                @endif
                            </p>
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $originalName = $item->originalTeacher->user->name ?? '-';
                                    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                                    $color  = $colors[$item->id % count($colors)];
                                @endphp

                                <div class="avatar-circle" style="background: {{ $color }};">
                                    {{ $originalName !== '-' ? strtoupper(substr($originalName, 0, 1)) : '-' }}
                                </div>

                                <div>
                                    <p class="table-main-text">{{ $originalName }}</p>
                                    <p class="table-sub-text">Original</p>
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $substituteName = $item->substituteTeacher->user->name ?? '-';
                                    $subColor  = $colors[($item->id + 2) % count($colors)];
                                @endphp

                                <div class="avatar-circle" style="background: {{ $subColor }};">
                                    {{ $substituteName !== '-' ? strtoupper(substr($substituteName, 0, 1)) : '-' }}
                                </div>

                                <div>
                                    <p class="table-main-text">{{ $substituteName }}</p>
                                    <p class="table-sub-text">Substitute</p>
                                </div>
                            </div>
                        </td>

                        <td>
                            <p class="table-main-text">
                                {{ $item->changedBy->name ?? '-' }}
                            </p>

                            <p class="table-sub-text">
                                {{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d M Y, H:i') : '-' }}
                            </p>
                        </td>

                        <td>
                            <div class="tag-wrap">
                                <span class="role-tag">
                                    {{ \Illuminate\Support\Str::limit($item->reason ?: '-', 45) }}
                                </span>
                            </div>
                        </td>

                        <td>
                            <div class="action-row">
                                <a href="{{ route('admin.timetable-substitutions.show', $item->id) }}" class="btn-outline">
                                    <i class="fas fa-eye"></i>
                                    View
                                </a>

                                <a href="{{ route('admin.timetable-substitutions.edit', $item->id) }}" class="btn-outline btn-outline-edit">
                                    <i class="fas fa-pencil-alt"></i>
                                    Edit
                                </a>

                                <form action="{{ route('admin.timetable-substitutions.destroy', $item->id) }}"
                                      method="POST"
                                      style="display:inline;"
                                      onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
                                    @method('DELETE')
                                    @csrf

                                    <button type="submit" class="btn-outline btn-outline-danger">
                                        <i class="fas fa-trash-alt"></i>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('scripts')
@parent
<script>
$(function () {
    if ($.fn.DataTable.isDataTable('.datatable-Substitutions')) {
        $('.datatable-Substitutions').DataTable().destroy();
    }

    $('.datatable-Substitutions').DataTable({
        pageLength: 25,
        order: [],
        language: {
            search: '',
            searchPlaceholder: 'Search substitute assignments...',
            info: 'Showing _START_–_END_ of _TOTAL_ assignments'
        }
    });
});
</script>
@endsection