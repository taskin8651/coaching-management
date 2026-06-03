@extends('layouts.admin')
@section('page-title','Report Cards')
@section('content')
<div class="admin-page-head"><div><h2 class="admin-page-title">Report Cards</h2><p class="admin-page-subtitle">Generated result summaries for parent visibility</p></div></div>
@can('report_card_create')<form method="POST" action="{{ route('admin.report-cards.generate') }}" class="page-card" style="padding:16px;margin-bottom:16px">@csrf <div class="action-row"><input name="exam_id" required placeholder="Exam ID" class="field-input" style="width:160px"><button class="btn-primary">Generate</button></div></form>@endcan
<div class="page-card"><div class="page-card-table"><table class="min-w-full datatable datatable-ReportCards"><thead><tr><th>Student</th><th>Exam</th><th>Marks</th><th>%</th><th>Grade</th><th>Rank</th><th>Parent</th><th>Publish</th></tr></thead><tbody>@foreach($reportCards as $card)<tr><td>{{ $card->student->user->name ?? '-' }}</td><td>{{ $card->exam->title ?? '-' }}</td><td>{{ $card->marks_obtained }}/{{ $card->total_marks }}</td><td>{{ $card->percentage }}</td><td>{{ $card->grade }}</td><td>{{ $card->rank }}</td><td>{{ $card->published_to_parent ? 'Published' : 'Draft' }}</td><td>@can('report_card_publish')<form method="POST" action="{{ route('admin.report-cards.publish',$card->id) }}">@csrf<button class="btn-outline">Publish</button></form>@endcan</td></tr>@endforeach</tbody></table></div></div>
@endsection
@section('scripts')@parent<script>$(function(){initAdminDataTable('.datatable-ReportCards',{searchPlaceholder:'Search report cards...'});});</script>@endsection
