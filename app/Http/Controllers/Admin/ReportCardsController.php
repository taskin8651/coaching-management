<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ReportCardsExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Models\Batch;
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
use Illuminate\Support\Arr;
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
        $batches = $this->scopeBatchQuery(Batch::where('status', 'active'))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $testTypes = $this->scopeBranchQuery(Exam::query())
            ->whereNotNull('exam_type')
            ->distinct()
            ->orderBy('exam_type')
            ->pluck('exam_type', 'exam_type');

        $students = $this->scopeStudentQuery(Student::query())
            ->with(['user', 'studentBatches'])
            ->get()
            ->mapWithKeys(fn ($student) => [$student->id => $student->user->name ?? ($student->student_code ?? 'Student #'.$student->id)])
            ->sort();

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

        $filterOptions = $this->reportCardFilterOptions();

        return view('admin.reportCards.index', compact(
            'reportCards',
            'exams',
            'summary',
            'filters',
            'batches',
            'testTypes',
            'students',
            'teachers',
            'subjects',
            'courses',
            'filterOptions'
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

        $data = $this->validateReportCardSelection($request, true);

        $exams = $this->matchingExamsForSelection($data)->get();

        abort_if($exams->isEmpty(), Response::HTTP_UNPROCESSABLE_ENTITY, 'No completed tests found for the selected report-card criteria.');

        $studentIds = $this->selectedStudentIds($data);

        $results = ExamResult::with('exam')
            ->whereIn('exam_id', $exams->pluck('id'))
            ->when($studentIds->isNotEmpty(), fn ($q) => $q->whereIn('student_id', $studentIds))
            ->get();

        abort_if($results->isEmpty(), Response::HTTP_UNPROCESSABLE_ENTITY, 'Selected exam has no results entered yet.');

        foreach ($results as $result) {
            $exam = $result->exam;

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

        return back()->with('message', 'Report cards generated successfully for '.$results->count().' result row(s).');
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
        $reportCards = ReportCard::with(['student.user', 'batch', 'exam.subject', 'exam.course', 'exam.batch']);

        $scope = $this->erpScope();

        if ($scope['is_student'] && $scope['student_id']) {
            $reportCards->where('student_id', $scope['student_id'])->where('published_to_parent', true);
        } elseif ($scope['is_parent'] && $scope['parent_student_ids']->isNotEmpty()) {
            $reportCards->whereIn('student_id', $scope['parent_student_ids'])->where('published_to_parent', true);
        } elseif (! $scope['is_admin']) {
            $reportCards->whereHas('student', fn ($q) => $this->scopeStudentQuery($q));
        }

        $filters = $this->reportCardFilters($request);

        if (! empty($filters['student_ids'])) {
            $reportCards->whereIn('student_id', $filters['student_ids']);
        }

        if (! empty($filters['subject_ids'])) {
            $reportCards->whereHas('exam', fn ($q) => $q->whereIn('subject_id', $filters['subject_ids']));
        } elseif (! empty($filters['subject_id'])) {
            $reportCards->whereHas('exam', fn ($q) => $q->where('subject_id', $filters['subject_id']));
        }

        if (! empty($filters['batch_id'])) {
            $reportCards->whereHas('exam', fn ($q) => $q->where('batch_id', $filters['batch_id']));
        }

        if (! empty($filters['exam_types'])) {
            $reportCards->whereHas('exam', fn ($q) => $q->whereIn('exam_type', $filters['exam_types']));
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

    private function validateReportCardSelection(Request $request, bool $batchRequired = false): array
    {
        $rules = [
            'batch_id' => [$batchRequired ? 'required' : 'nullable', 'integer', 'exists:batches,id'],
            'report_month' => ['nullable', 'date_format:Y-m'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'exam_types' => ['nullable', 'array'],
            'exam_types.*' => ['string', 'max:255'],
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['integer', 'exists:students,id'],
            'all_students' => ['nullable', 'boolean'],
        ];

        $data = $request->validate($rules);

        return $this->normalizeReportCardSelection($data);
    }

    private function reportCardFilters(Request $request): array
    {
        $data = $this->normalizeReportCardSelection($request->only([
            'batch_id',
            'report_month',
            'date_from',
            'date_to',
            'exam_types',
            'subject_ids',
            'student_ids',
            'all_students',
            'student_id',
            'teacher_id',
            'subject_id',
            'course_id',
        ]));

        foreach (['student_id', 'teacher_id', 'subject_id', 'course_id'] as $legacyFilter) {
            $data[$legacyFilter] = $request->input($legacyFilter);
        }

        return $data;
    }

    private function normalizeReportCardSelection(array $data): array
    {
        foreach (['exam_types', 'subject_ids', 'student_ids'] as $key) {
            $data[$key] = collect(Arr::wrap($data[$key] ?? []))
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->values()
                ->all();
        }

        if (! empty($data['report_month'])) {
            $month = \Carbon\Carbon::createFromFormat('Y-m', $data['report_month']);
            $data['date_from'] = $data['date_from'] ?? $month->copy()->startOfMonth()->toDateString();
            $data['date_to'] = $data['date_to'] ?? $month->copy()->endOfMonth()->toDateString();
        }

        return $data;
    }

    private function matchingExamsForSelection(array $data)
    {
        return $this->scopeBranchQuery(Exam::where('status', 'completed'))
            ->where('batch_id', $data['batch_id'])
            ->when(! empty($data['date_from']), fn ($q) => $q->whereDate('exam_date', '>=', $data['date_from']))
            ->when(! empty($data['date_to']), fn ($q) => $q->whereDate('exam_date', '<=', $data['date_to']))
            ->when(! empty($data['exam_types']), fn ($q) => $q->whereIn('exam_type', $data['exam_types']))
            ->when(! empty($data['subject_ids']), fn ($q) => $q->whereIn('subject_id', $data['subject_ids']));
    }

    private function selectedStudentIds(array $data)
    {
        if (! empty($data['all_students']) && $data['all_students']) {
            return collect();
        }

        if (empty($data['batch_id'])) {
            return $this->scopeStudentQuery(Student::whereIn('id', $data['student_ids'] ?? []))
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values();
        }

        if (! empty($data['student_ids'])) {
            return $this->scopeStudentQuery(Student::whereIn('id', $data['student_ids']))
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values();
        }

        return $this->scopeStudentQuery(Student::query())
            ->where(function ($q) use ($data) {
                $q->where('batch_id', $data['batch_id'])
                    ->orWhereHas('studentBatches', fn ($sq) => $sq->where('batch_id', $data['batch_id'])->where('status', 'active'));
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();
    }

    private function reportCardFilterOptions(): array
    {
        $batches = $this->scopeBatchQuery(Batch::with(['subjects' => fn ($q) => $q->where('status', 'active')->orderBy('name')]))
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);

        $students = $this->scopeStudentQuery(Student::with(['user', 'studentBatches']))
            ->get(['id', 'user_id', 'batch_id', 'student_code']);

        return [
            'subjectsByBatch' => $batches->mapWithKeys(fn ($batch) => [
                $batch->id => $batch->subjects->map(fn ($subject) => [
                    'id' => $subject->id,
                    'name' => $subject->name,
                ])->values(),
            ]),
            'studentsByBatch' => $batches->mapWithKeys(fn ($batch) => [
                $batch->id => $students
                    ->filter(fn ($student) => (int) $student->batch_id === (int) $batch->id
                        || $student->studentBatches->where('status', 'active')->where('batch_id', $batch->id)->isNotEmpty())
                    ->map(fn ($student) => [
                        'id' => $student->id,
                        'name' => trim(($student->user->name ?? 'Student').' '.($student->student_code ? '('.$student->student_code.')' : '')),
                    ])
                    ->sortBy('name')
                    ->values(),
            ]),
        ];
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
