<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ReportCardsExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ReportCard;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Services\WhatsappService;
use Gate;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class ReportCardsController extends Controller
{
    use AppliesErpScope;

    public function index(Request $request)
    {
        abort_if(Gate::denies('report_card_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        [$reportCards, $summary, $filters] = $this->filteredReportCards($request);

        $exams = $this->examsForGeneration();

        $students = $this->scopeStudentQuery(Student::query())
            ->with('user')
            ->get()
            ->mapWithKeys(fn ($student) => [$student->id => $student->user->name ?? ($student->student_code ?? 'Student #'.$student->id)])
            ->sort()
            ->prepend('All Students', '');

        $teachers = $this->scopeBranchQuery(Teacher::where('status', 'active'))
            ->with('user')
            ->get()
            ->mapWithKeys(fn ($teacher) => [$teacher->id => $teacher->user->name ?? 'Teacher #'.$teacher->id])
            ->sort()
            ->prepend('All Teachers', '');

        $subjects = $this->scopeBranchQuery(Subject::where('status', 'active'))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend('All Subjects', '');

        $courses = $this->scopeBranchQuery(Course::where('status', 'active'))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend('All Courses', '');

        return view('admin.reportCards.index', compact(
            'reportCards',
            'exams',
            'summary',
            'filters',
            'students',
            'teachers',
            'subjects',
            'courses'
        ));
    }

    public function export(Request $request)
    {
        abort_if(Gate::denies('report_card_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        [$reportCards, $summary] = $this->filteredReportCards($request);

        return Excel::download(new ReportCardsExport($reportCards, $summary), 'report-cards-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function generate(Request $request)
    {
        abort_if(Gate::denies('report_card_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validate(['exam_id' => ['required', 'exists:exams,id']]);

        $exam = Exam::findOrFail($data['exam_id']);

        if (! $this->erpScope()['is_admin']) {
            $this->assertBranchAccess($exam);
        }

        $results = ExamResult::where('exam_id', $exam->id)->get();

        abort_if($results->isEmpty(), Response::HTTP_UNPROCESSABLE_ENTITY, 'Selected exam has no results entered yet.');

        foreach ($results as $result) {
            ReportCard::updateOrCreate(
                ['student_id' => $result->student_id, 'exam_id' => $exam->id],
                [
                    'batch_id'       => $exam->batch_id,
                    'total_marks'    => $result->total_marks,
                    'marks_obtained' => $result->marks_obtained,
                    'percentage'     => $result->percentage,
                    'grade'          => $this->grade($result->percentage),
                    'rank'           => $result->rank,
                    'remarks'        => $result->remarks,
                ]
            );
        }

        return back()->with('message', 'Report cards generated successfully.');
    }

    public function publish(ReportCard $reportCard, WhatsappService $whatsapp)
    {
        abort_if(Gate::denies('report_card_publish'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->assertBranchAccess($reportCard->student);

        $reportCard->update(['published_to_parent' => true, 'published_at' => now()->toDateString()]);

        $whatsapp->sendStudentGuardianMessage($reportCard->student, 'result', 'Result published. Percentage: '.$reportCard->percentage.'%, Grade: '.$reportCard->grade);

        return back()->with('message', 'Report card published successfully.');
    }

    private function filteredReportCards(Request $request): array
    {
        $reportCards = ReportCard::with(['student.user', 'exam.subject', 'exam.course', 'exam.batch']);

        $scope = $this->erpScope();

        if ($scope['is_student'] && $scope['student_id']) {
            $reportCards->where('student_id', $scope['student_id'])->where('published_to_parent', true);
        } elseif ($scope['is_parent'] && $scope['parent_student_ids']->isNotEmpty()) {
            $reportCards->whereIn('student_id', $scope['parent_student_ids'])->where('published_to_parent', true);
        } elseif (! $scope['is_admin']) {
            $reportCards->whereHas('student', fn ($q) => $this->scopeStudentQuery($q));
        }

        $filters = $request->only(['student_id', 'teacher_id', 'subject_id', 'course_id', 'date_from', 'date_to']);

        if (! empty($filters['student_id'])) {
            $reportCards->where('student_id', $filters['student_id']);
        }

        if (! empty($filters['subject_id'])) {
            $reportCards->whereHas('exam', fn ($q) => $q->where('subject_id', $filters['subject_id']));
        }

        if (! empty($filters['course_id'])) {
            $reportCards->whereHas('exam', fn ($q) => $q->where('course_id', $filters['course_id']));
        }

        if (! empty($filters['date_from'])) {
            $reportCards->whereHas('exam', fn ($q) => $q->whereDate('exam_date', '>=', $filters['date_from']));
        }

        if (! empty($filters['date_to'])) {
            $reportCards->whereHas('exam', fn ($q) => $q->whereDate('exam_date', '<=', $filters['date_to']));
        }

        if (! empty($filters['teacher_id'])) {
            $assignments = TeacherAssignment::where('teacher_id', $filters['teacher_id'])
                ->where('status', 'active')
                ->get(['subject_id', 'batch_id']);

            $subjectIds = $assignments->pluck('subject_id')->filter()->values();
            $batchIds = $assignments->pluck('batch_id')->filter()->values();

            $reportCards->whereHas('exam', function ($q) use ($subjectIds, $batchIds) {
                $q->where(function ($qq) use ($subjectIds, $batchIds) {
                    $qq->whereIn('subject_id', $subjectIds)
                        ->orWhereIn('batch_id', $batchIds);
                });
            });
        }

        $reportCards = $reportCards->latest()->get();

        $gradableCards = $reportCards->filter(fn ($card) => $card->exam && $card->exam->passing_marks !== null);

        $passCount = $gradableCards->filter(fn ($card) => $card->marks_obtained >= $card->exam->passing_marks)->count();
        $failCount = $gradableCards->count() - $passCount;

        $summary = [
            'total'              => $reportCards->count(),
            'average_percentage' => $reportCards->count() ? round($reportCards->avg('percentage'), 1) : 0,
            'pass_count'         => $passCount,
            'fail_count'         => $failCount,
            'pass_percentage'    => $reportCards->count() ? round($passCount / $reportCards->count() * 100, 1) : 0,
            'grade_counts'       => $reportCards->groupBy(fn ($card) => $card->grade ?: 'Ungraded')->map->count(),
        ];

        return [$reportCards, $summary, $filters];
    }

    private function grade($percentage): string
    {
        return $percentage >= 90 ? 'A+' : ($percentage >= 75 ? 'A' : ($percentage >= 60 ? 'B' : ($percentage >= 45 ? 'C' : 'D')));
    }

    private function examsForGeneration()
    {
        return $this->scopeBranchQuery(Exam::where('status', 'completed'))
            ->with('batch')
            ->latest('exam_date')
            ->get()
            ->mapWithKeys(fn ($exam) => [$exam->id => $exam->title.' — '.($exam->batch->name ?? 'No Batch').' — '.(optional($exam->exam_date)->format('d M Y') ?? '-')])
            ->prepend(trans('global.pleaseSelect'), '');
    }
}
