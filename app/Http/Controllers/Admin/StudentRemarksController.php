<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Models\Student;
use App\Models\StudentRemark;
use App\Models\Teacher;
use App\Services\WhatsappService;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StudentRemarksController extends Controller
{
    use AppliesErpScope;

    public function index() { abort_if(Gate::denies('student_remark_access'), Response::HTTP_FORBIDDEN, '403 Forbidden'); $remarks = StudentRemark::with(['student.user','teacher.user','createdBy']); $scope = $this->erpScope(); if ($scope['is_student'] && $scope['student_id']) { $remarks->where('student_id', $scope['student_id'])->where('visible_to_parent', true); } elseif ($scope['is_parent'] && $scope['parent_student_ids']->isNotEmpty()) { $remarks->whereIn('student_id', $scope['parent_student_ids'])->where('visible_to_parent', true); } elseif ($scope['is_teacher'] && $scope['teacher_id']) { $remarks->where('teacher_id', $scope['teacher_id']); } elseif (! $scope['is_admin']) { $remarks->whereHas('student', fn ($q) => $this->scopeStudentQuery($q)); } $remarks = $remarks->latest()->get(); return view('admin.studentRemarks.index', compact('remarks')); }
    public function create() { abort_if(Gate::denies('student_remark_create'), Response::HTTP_FORBIDDEN, '403 Forbidden'); return view('admin.studentRemarks.create', $this->formData()); }
    public function store(Request $request, WhatsappService $whatsapp) { abort_if(Gate::denies('student_remark_create'), Response::HTTP_FORBIDDEN, '403 Forbidden'); $remark = StudentRemark::create($this->validated($request) + ['created_by_id'=>auth()->id()]); if ($remark->visible_to_parent) { $whatsapp->sendStudentGuardianMessage($remark->student, 'remark', ucfirst($remark->remark_type).' remark added: '.$remark->remark); } return redirect()->route('admin.student-remarks.index')->with('message', 'Remark saved successfully.'); }
    private function formData(): array { return ['students'=>$this->scopeStudentQuery(Student::with('user'))->get()->mapWithKeys(fn($s)=>[$s->id=>$s->user->name ?? $s->student_code ?? 'Student #'.$s->id])->prepend(trans('global.pleaseSelect'),''),'teachers'=>$this->scopeBranchQuery(Teacher::with('user'))->get()->mapWithKeys(fn($t)=>[$t->id=>$t->user->name ?? 'Teacher #'.$t->id])->prepend('Optional','')]; }
    private function validated(Request $request): array { return $request->validate(['student_id'=>['required','exists:students,id'],'teacher_id'=>['nullable','exists:teachers,id'],'remark_type'=>['required','in:positive,negative,neutral'],'remark_date'=>['required','date'],'title'=>['nullable','string','max:255'],'remark'=>['required','string'],'visible_to_parent'=>['nullable','boolean']]); }
}
