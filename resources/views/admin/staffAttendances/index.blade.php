@extends('layouts.admin')
@section('page-title', 'Staff Attendance')
@section('content')
<div class="admin-page-head"><div><h2 class="admin-page-title">Staff Attendance</h2><p class="admin-page-subtitle">Branch-wise staff attendance records.</p></div>@can('staff_attendance_create')<a href="{{ route('admin.staff-attendances.create') }}" class="btn-primary"><i class="fas fa-plus"></i> Mark Staff Attendance</a>@endcan</div>

<form method="GET" action="{{ route('admin.staff-attendances.index') }}" class="page-card mb-4">

    <div class="page-card-header">
        <p class="page-card-title">Filters</p>
        <span class="page-card-note">
            <i class="fas fa-filter"></i>
            Branch, status ya date range se records filter karein
        </span>
    </div>

    <div class="p-4">

        <div class="filter-grid">

            @if(count($branches) > 1)
            <div class="filter-item">
                <label class="field-label">Branch</label>
                <select name="branch_id" class="field-input">
                    @foreach($branches as $id => $name)
                        <option value="{{ $id }}" {{ (string) old('branch_id', $filters['branch_id'] ?? '') === (string) $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="filter-item">
                <label class="field-label">Status</label>
                <select name="status" class="field-input">
                    @php $selectedStatus = old('status', $filters['status'] ?? ''); @endphp
                    <option value="" {{ $selectedStatus === '' ? 'selected' : '' }}>All Status</option>
                    <option value="present" {{ $selectedStatus === 'present' ? 'selected' : '' }}>Present</option>
                    <option value="absent" {{ $selectedStatus === 'absent' ? 'selected' : '' }}>Absent</option>
                    <option value="late" {{ $selectedStatus === 'late' ? 'selected' : '' }}>Late</option>
                    <option value="half_day" {{ $selectedStatus === 'half_day' ? 'selected' : '' }}>Half Day</option>
                    <option value="leave" {{ $selectedStatus === 'leave' ? 'selected' : '' }}>Leave</option>
                </select>
            </div>

            <div class="filter-item">
                <label class="field-label">From</label>
                <input type="date"
                       name="date_from"
                       class="field-input"
                       value="{{ old('date_from', $filters['date_from'] ?? '') }}">
            </div>

            <div class="filter-item">
                <label class="field-label">To</label>
                <input type="date"
                       name="date_to"
                       class="field-input"
                       value="{{ old('date_to', $filters['date_to'] ?? '') }}">
            </div>

            <div class="filter-item">
                <button type="submit" class="btn-primary w-100">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>

            <div class="filter-item">
                <a href="{{ route('admin.staff-attendances.index') }}" class="btn-ghost w-100 text-center">
                    Reset
                </a>
            </div>

        </div>

    </div>

</form>
<style>
    .filter-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:20px;
    align-items:end;
}

.filter-item{
    display:flex;
    flex-direction:column;
}

.field-label{
    margin-bottom:8px;
    font-size:14px;
    font-weight:600;
}

.field-input{
    width:100%;
    height:46px;
}

.btn-primary,
.btn-ghost{
    height:46px;
    display:flex;
    align-items:center;
    justify-content:center;
}

.w-100{
    width:100%;
}

@media (max-width:768px){
    .filter-grid{
        grid-template-columns:1fr;
    }
}
</style>

<div class="stats-grid"><div class="stat-card"><p class="stat-label">Total Records</p><p class="stat-value">{{ $attendances->count() }}</p></div><div class="stat-card"><p class="stat-label">Present</p><p class="stat-value">{{ $attendances->where('status','present')->count() }}</p></div><div class="stat-card"><p class="stat-label">Absent</p><p class="stat-value">{{ $attendances->where('status','absent')->count() }}</p></div><div class="stat-card"><p class="stat-label">Late / Half Day</p><p class="stat-value">{{ $attendances->whereIn('status',['late','half_day'])->count() }}</p></div></div>
<div class="page-card"><div class="page-card-header"><p class="page-card-title">Staff Attendance Records</p><span class="page-card-note"><i class="fas fa-building"></i> Branch-wise</span></div><div class="page-card-table"><table class="min-w-full datatable datatable-StaffAttendance"><thead><tr><th>Date</th><th>Staff</th><th>Branch</th><th>Time</th><th>Worked</th><th>Status</th><th>Source</th></tr></thead><tbody>@foreach($attendances as $attendance)<tr><td>{{ optional($attendance->attendance_date)->format('d M Y') ?? '-' }}</td><td><p class="table-main-text">{{ $attendance->staff->user->name ?? 'Staff' }}</p></td><td><p class="table-main-text">{{ $attendance->branch->name ?? '-' }}</p><p class="table-sub-text">Staff Branch</p></td><td><span class="code-pill">{{ $attendance->first_in_time ?? '-' }} - {{ $attendance->last_out_time ?? '-' }}</span></td><td>{{ $attendance->worked_minutes }} min</td><td><span class="status-pill {{ $attendance->status === 'present' ? 'success' : ($attendance->status === 'late' ? 'warning' : '') }}">{{ ucfirst(str_replace('_',' ',$attendance->status)) }}</span></td><td><span class="status-pill" style="background:#EDE9FE;color:#6D28D9;">{{ ucfirst($attendance->source) }}</span></td></tr>@endforeach</tbody></table></div></div>
@endsection
@section('scripts')@parent<script>$(function(){initAdminDataTable('.datatable-StaffAttendance',{searchPlaceholder:'Search staff attendance...',infoText:'Showing _START_–_END_ of _TOTAL_ records'});});</script>@endsection
