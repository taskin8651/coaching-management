<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Models\Batch;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ReportCard;
use App\Models\Student;
use App\Services\WhatsappService;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReportCardsController extends Controller
{
    use AppliesErpScope;

    public function index() { abort_if(Gate::denies('report_card_access'), Response::HTTP_FORBIDDEN, '403 Forbidden'); $reportCards=ReportCard::with(['student.user','exam','batch']); $scope = $this->erpScope(); if ($scope['is_student'] && $scope['student_id']) { $reportCards->where('student_id', $scope['student_id'])->where('published_to_parent', true); } elseif ($scope['is_parent'] && $scope['parent_student_ids']->isNotEmpty()) { $reportCards->whereIn('student_id', $scope['parent_student_ids'])->where('published_to_parent', true); } elseif (! $scope['is_admin']) { $reportCards->whereHas('student', fn ($q) => $this->scopeStudentQuery($q)); } $reportCards=$reportCards->latest()->get(); $exams=$this->examsForGeneration(); return view('admin.reportCards.index', compact('reportCards', 'exams')); }
    public function generate(Request $request) { abort_if(Gate::denies('report_card_create'), Response::HTTP_FORBIDDEN, '403 Forbidden'); $data=$request->validate(['exam_id'=>['required','exists:exams,id']]); $exam=Exam::findOrFail($data['exam_id']); if (! $this->erpScope()['is_admin']) { $this->assertBranchAccess($exam); } $results = ExamResult::where('exam_id',$exam->id)->get(); abort_if($results->isEmpty(), Response::HTTP_UNPROCESSABLE_ENTITY, 'Selected exam has no results entered yet.'); foreach($results as $result){ ReportCard::updateOrCreate(['student_id'=>$result->student_id,'exam_id'=>$exam->id], ['batch_id'=>$exam->batch_id,'total_marks'=>$result->total_marks,'marks_obtained'=>$result->marks_obtained,'percentage'=>$result->percentage,'grade'=>$this->grade($result->percentage),'rank'=>$result->rank,'remarks'=>$result->remarks]); } return back()->with('message','Report cards generated successfully.'); }
    public function publish(ReportCard $reportCard, WhatsappService $whatsapp) { abort_if(Gate::denies('report_card_publish'), Response::HTTP_FORBIDDEN, '403 Forbidden'); $this->assertBranchAccess($reportCard->student); $reportCard->update(['published_to_parent'=>true,'published_at'=>now()->toDateString()]); $whatsapp->sendStudentGuardianMessage($reportCard->student,'result','Result published. Percentage: '.$reportCard->percentage.'%, Grade: '.$reportCard->grade); return back()->with('message','Report card published successfully.'); }
    private function grade($percentage): string { return $percentage >= 90 ? 'A+' : ($percentage >= 75 ? 'A' : ($percentage >= 60 ? 'B' : ($percentage >= 45 ? 'C' : 'D'))); }
    private function examsForGeneration() { return $this->scopeBranchQuery(Exam::where('status', 'completed'))->with('batch')->latest('exam_date')->get()->mapWithKeys(fn ($exam) => [$exam->id => $exam->title.' — '.($exam->batch->name ?? 'No Batch').' — '.(optional($exam->exam_date)->format('d M Y') ?? '-')])->prepend(trans('global.pleaseSelect'), ''); }
}
