@extends('layouts.admin')
@section('page-title', 'Teacher Attendance')
@section('content')
<div class="admin-page-head"><div><h2 class="admin-page-title">Teacher Attendance</h2><p class="admin-page-subtitle">Batch-wise teacher attendance records.</p></div>@can('teacher_attendance_create')<a href="{{ route('admin.teacher-attendances.create') }}" class="btn-primary"><i class="fas fa-plus"></i> Mark Teacher Attendance</a>@endcan</div>

<form method="GET" action="{{ route('admin.teacher-attendances.index') }}" class="page-card mb-4">

    <div class="page-card-header">
        <p class="page-card-title">Filters</p>
        <span class="page-card-note">
            <i class="fas fa-filter"></i>
            Branch, batch, status ya date range se records filter karein
        </span>
    </div>

    <div class="p-4">

        <div class="filter-grid">

            @if(count($branches) > 1)
            <div class="filter-item">
                <label class="field-label" for="branch_id">Branch</label>
                <select name="branch_id" id="branch_id" class="field-input">
                    @foreach($branches as $id => $name)
                        <option value="{{ $id }}" {{ (string) old('branch_id', $filters['branch_id'] ?? '') === (string) $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="filter-item">
                <label class="field-label" for="batch_id">Batch</label>
                <select name="batch_id" id="batch_id" class="field-input">
                    @foreach($batches as $id => $name)
                        <option value="{{ $id }}" {{ (string) old('batch_id', $filters['batch_id'] ?? '') === (string) $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-item">
                <label class="field-label" for="status">Status</label>
                <select name="status" id="status" class="field-input">
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
                <label class="field-label" for="date_from">From</label>
                <input type="date"
                       name="date_from"
                       id="date_from"
                       class="field-input"
                       value="{{ old('date_from', $filters['date_from'] ?? '') }}">
            </div>

            <div class="filter-item">
                <label class="field-label" for="date_to">To</label>
                <input type="date"
                       name="date_to"
                       id="date_to"
                       class="field-input"
                       value="{{ old('date_to', $filters['date_to'] ?? '') }}">
            </div>

            <div class="filter-item">
                <button type="submit" class="btn-primary w-100">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>

            <div class="filter-item">
                <a href="{{ route('admin.teacher-attendances.index') }}" class="btn-ghost w-100 text-center">
                    Reset
                </a>
            </div>

        </div>

    </div>

</form>

<style>
    /* ===========================
   Filter Grid
=========================== */

.filter-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:18px;
    align-items:end;
}

.filter-item{
    display:flex;
    flex-direction:column;
}

.field-label{
    display:block;
    margin-bottom:8px;
    font-size:13px;
    font-weight:600;
    color:#374151;
}

.field-input{
    width:100%;
    height:46px;
    padding:0 14px;
    border:1px solid #dbe2ea;
    border-radius:10px;
    background:#fff;
    font-size:14px;
    outline:none;
    transition:.25s ease;
}

.field-input:focus{
    border-color:#0d6efd;
    box-shadow:0 0 0 .2rem rgba(13,110,253,.15);
}

select.field-input{
    cursor:pointer;
}

/* ===========================
   Buttons
=========================== */

.filter-grid .btn-primary,
.filter-grid .btn-ghost{
    width:100%;
    height:46px;
    border-radius:10px;
    font-size:14px;
    font-weight:600;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    transition:.25s;
    text-decoration:none;
}

.btn-primary{
    background:#0d6efd;
    color:#fff;
    border:none;
}

.btn-primary:hover{
    background:#0b5ed7;
    color:#fff;
}

.btn-ghost{
    background:#fff;
    color:#374151;
    border:1px solid #dbe2ea;
}

.btn-ghost:hover{
    background:#f8fafc;
    color:#111827;
}

/* ===========================
   Card
=========================== */

.page-card{
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius:14px;
    overflow:hidden;
}

.page-card-header{
    padding:18px 22px;
    border-bottom:1px solid #eef2f7;
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:10px;
}

.page-card-title{
    margin:0;
    font-size:18px;
    font-weight:700;
}

.page-card-note{
    font-size:13px;
    color:#6b7280;
}

.page-card-body{
    padding:20px;
}

/* ===========================
   Responsive
=========================== */

@media (max-width:1200px){
    .filter-grid{
        grid-template-columns:repeat(3,minmax(180px,1fr));
    }
}

@media (max-width:768px){
    .filter-grid{
        grid-template-columns:repeat(2,minmax(150px,1fr));
    }
}

@media (max-width:576px){
    .filter-grid{
        grid-template-columns:1fr;
    }

    .page-card-header{
        flex-direction:column;
        align-items:flex-start;
    }

    .filter-grid .btn-primary,
    .btn-ghost{
        width:100%;
    }
}
</style>

<div class="stats-grid"><div class="stat-card"><p class="stat-label">Total Records</p><p class="stat-value">{{ $attendances->count() }}</p></div><div class="stat-card"><p class="stat-label">Present</p><p class="stat-value">{{ $attendances->where('status','present')->count() }}</p></div><div class="stat-card"><p class="stat-label">Absent</p><p class="stat-value">{{ $attendances->where('status','absent')->count() }}</p></div><div class="stat-card"><p class="stat-label">Batches</p><p class="stat-value">{{ $attendances->pluck('batch_id')->filter()->unique()->count() }}</p></div></div>
<div class="page-card"><div class="page-card-header"><p class="page-card-title">Teacher Attendance Records</p><span class="page-card-note"><i class="fas fa-chalkboard-teacher"></i> Batch-wise</span></div><div class="page-card-table"><table class="min-w-full datatable datatable-TeacherAttendance"><thead><tr><th>Date</th><th>Teacher</th><th>Branch</th><th>Time</th><th>Worked</th><th>Status</th><th>Source</th></tr></thead><tbody>@foreach($attendances as $attendance)<tr><td>{{ optional($attendance->attendance_date)->format('d M Y') ?? '-' }}</td><td>{{ $attendance->teacher->user->name ?? 'Teacher' }}</td><td>{{ $attendance->branch->name ?? '-' }}</td><td><span class="code-pill">{{ $attendance->first_in_time ?? '-' }} - {{ $attendance->last_out_time ?? '-' }}</span></td><td>{{ $attendance->worked_minutes }} min</td><td><span class="status-pill {{ $attendance->status === 'present' ? 'success' : ($attendance->status === 'late' ? 'warning' : '') }}">{{ ucfirst(str_replace('_',' ',$attendance->status)) }}</span></td><td><span class="status-pill" style="background:#EDE9FE;color:#6D28D9;">{{ ucfirst($attendance->source) }}</span></td></tr>@endforeach</tbody></table></div></div>
@endsection
@section('scripts')@parent<script>$(function(){initAdminDataTable('.datatable-TeacherAttendance',{searchPlaceholder:'Search teacher attendance...',infoText:'Showing _START_–_END_ of _TOTAL_ records'});});</script>@endsection
