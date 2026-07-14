@extends('layouts.admin')

@section('page-title', 'Assign Student Subjects')

@section('styles')
<style>
:root{
    --primary:#2563eb;
    --primary-dark:#1d4ed8;
    --secondary:#0f172a;
    --success:#10b981;
    --border:#e2e8f0;
    --bg:#f8fafc;
    --text:#1e293b;
    --muted:#64748b;
    --white:#fff;
    --shadow:0 15px 40px rgba(15,23,42,.08);
    --shadow-hover:0 25px 50px rgba(15,23,42,.12);
    --radius:18px;
    --transition:.3s ease;
}

.assignment-toolbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:18px;
    padding:22px;
    background:linear-gradient(135deg,#ffffff,#f8fbff);
    border:1px solid var(--border);
    border-radius:22px;
    box-shadow:var(--shadow);
    margin-bottom:22px;
}

.assignment-toolbar .field-group{
    margin:0;
    min-width:280px;
}

.assignment-toolbar .field-input{
    border-radius:14px;
    border:1px solid var(--border);
    min-height:48px;
    transition:.25s;
}

.assignment-toolbar .field-input:focus{
    border-color:var(--primary);
    box-shadow:0 0 0 .2rem rgba(37,99,235,.15);
}

.matrix-stats{
    display:flex;
    gap:15px;
    flex-wrap:wrap;
}

.matrix-stat{
    min-width:120px;
    text-align:center;
    padding:18px;
    border-radius:18px;
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    color:#fff;
    font-weight:700;
    box-shadow:0 10px 25px rgba(37,99,235,.25);
    transition:.25s;
}

.matrix-stat:hover{
    transform:translateY(-4px);
    box-shadow:0 18px 35px rgba(37,99,235,.35);
}

.matrix-card{
    background:#fff;
    border-radius:22px;
    overflow:hidden;
    border:1px solid var(--border);
    box-shadow:var(--shadow);
}

.matrix-loader{
    display:none;
    padding:60px;
    text-align:center;
    color:var(--muted);
    font-weight:700;
    font-size:16px;
}

.matrix-loader.active{
    display:block;
}

.matrix-scroll{
    max-height:72vh;
    overflow:auto;
    background:#fff;
}

.matrix-scroll::-webkit-scrollbar{
    width:10px;
    height:10px;
}

.matrix-scroll::-webkit-scrollbar-thumb{
    background:#cbd5e1;
    border-radius:20px;
}

.matrix-scroll::-webkit-scrollbar-thumb:hover{
    background:#94a3b8;
}

.assignment-matrix{
    margin:0;
    min-width:1000px;
    border-collapse:separate;
    border-spacing:0;
    background:#fff;
}

.assignment-matrix th,
.assignment-matrix td{
    padding:15px 14px!important;
    border:1px solid #edf2f7!important;
    vertical-align:middle;
}

.assignment-matrix thead th{
    position:sticky;
    top:0;
    z-index:30;
    background:linear-gradient(135deg,#1e3a8a,#2563eb);
    color:#fff;
    text-align:center;
    font-size:13px;
    font-weight:700;
    letter-spacing:.3px;
    white-space:nowrap;
}

.assignment-matrix thead th:first-child{
    border-top-left-radius:18px;
}

.assignment-matrix thead th:last-child{
    border-top-right-radius:18px;
}

.assignment-matrix tbody tr:nth-child(even){
    background:#fbfdff;
}

.assignment-matrix tbody tr:hover td{
    background:#eef5ff;
    transition:.2s;
}

.assignment-matrix tbody tr{
    transition:.25s;
}

.assignment-matrix .student-col{
    position:sticky;
    left:0;
    z-index:20;
    background:#fff;
    min-width:260px;
    box-shadow:10px 0 25px rgba(0,0,0,.04);
}

.assignment-matrix tbody tr:nth-child(even) .student-col{
    background:#fbfdff;
}

.assignment-matrix thead .student-col{
    background:linear-gradient(135deg,#0f172a,#1e3a8a);
    color:#fff;
    z-index:40;
}

.student-name{
    font-size:15px;
    font-weight:700;
    color:#0f172a;
    margin-bottom:3px;
}

.student-code{
    font-size:12px;
    color:#64748b;
}

.subject-head{
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    gap:8px;
    min-width:120px;
}

.subject-head span{
    font-weight:700;
    font-size:13px;
}

.subject-head small{
    font-size:11px;
    color:#dbeafe;
}

.matrix-check{
    width:20px;
    height:20px;
    cursor:pointer;
    accent-color:#2563eb;
    transform:scale(1.05);
    transition:.2s;
}

.matrix-check:hover{
    transform:scale(1.25);
}

.assignment-matrix td{
    text-align:center;
}

.assignment-matrix td.student-col{
    text-align:left;
}

.empty-matrix{
    padding:0px 20px;
    text-align:center;
    color:#64748b;
    font-size:16px;
}

.empty-matrix i{
    font-size:50px;
    color:#94a3b8;
    margin-bottom:15px;
}

.pagination-wrap{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
    padding:18px 22px;
    border-top:1px solid var(--border);
    background:#fff;
}

.page-info{
    font-size:14px;
    font-weight:600;
    color:#475569;
}

.pagination-wrap .btn{
    border-radius:12px;
    padding:9px 18px;
    font-weight:700;
}

.pagination-wrap .btn-primary{
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    border:none;
}

.pagination-wrap .btn-primary:hover{
    transform:translateY(-2px);
    box-shadow:0 10px 25px rgba(37,99,235,.25);
}

.assignment-matrix input[type=checkbox]{
    cursor:pointer;
}

.assignment-matrix td:hover{
    background:#eaf3ff;
}

@media(max-width:992px){

.assignment-toolbar{
    flex-direction:column;
    align-items:stretch;
}

.assignment-toolbar .field-group{
    width:100%;
    min-width:100%;
}

.matrix-stat{
    flex:1;
}

.assignment-matrix{
    min-width:900px;
}

}

@media(max-width:768px){

.matrix-scroll{
    max-height:65vh;
}

.student-col{
    min-width:200px!important;
}

.subject-head{
    min-width:90px;
}

.assignment-matrix th,
.assignment-matrix td{
    padding:10px!important;
}

}
</style>
@endsection

@section('content')
<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.student-batches.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">Assign Student Subjects</h2>
        <p class="admin-page-subtitle">Batch select karke Excel-style matrix me students ko subjects assign karein.</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.student-batches.store') }}" id="assignmentForm">
    @csrf

    @include('admin.studentBatches.matrix', [
        'selectedBatchId' => old('batch_id'),
        'selectedStatus' => old('status', 'active'),
        'oldAssignments' => old('assignments', []),
    ])

    <div class="form-actions mt-4">
        <button type="submit" class="btn-primary">
            <i class="fas fa-save"></i>
            Save Assignments
        </button>
        <a href="{{ route('admin.student-batches.index') }}" class="btn-ghost">{{ trans('global.cancel') }}</a>
    </div>
</form>
@endsection
