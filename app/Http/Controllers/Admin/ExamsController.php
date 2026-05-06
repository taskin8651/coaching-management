<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExamRequest;
use App\Http\Requests\StoreExamResultRequest;
use App\Http\Requests\UpdateExamRequest;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Student;
use App\Models\Subject;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExamsController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('exam_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $exams = Exam::with(['branch', 'course', 'batch', 'subject'])
            ->latest()
            ->get();

        return view('admin.exams.index', compact('exams'));
    }

    public function create()
    {
        abort_if(Gate::denies('exam_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branches = Branch::where('status', 'active')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $courses  = Course::where('status', 'active')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $batches  = Batch::where('status', 'active')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $subjects = Subject::where('status', 'active')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $examTypes = $this->examTypes();

        return view('admin.exams.create', compact('branches', 'courses', 'batches', 'subjects', 'examTypes'));
    }

    public function store(StoreExamRequest $request)
    {
        Exam::create($request->validated());

        return redirect()->route('admin.exams.index')->with('message', 'Exam created successfully.');
    }

    public function show(Exam $exam)
    {
        abort_if(Gate::denies('exam_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $exam->load(['branch', 'course', 'batch', 'subject', 'results.student.user']);

        $students = Student::with(['user'])
            ->when($exam->branch_id, function ($query) use ($exam) {
                $query->where('branch_id', $exam->branch_id);
            })
            ->when($exam->course_id, function ($query) use ($exam) {
                $query->where('course_id', $exam->course_id);
            })
            ->when($exam->batch_id, function ($query) use ($exam) {
                $query->where('batch_id', $exam->batch_id);
            })
            ->get();

        $existingResults = $exam->results->keyBy('student_id');

        return view('admin.exams.show', compact('exam', 'students', 'existingResults'));
    }

    public function edit(Exam $exam)
    {
        abort_if(Gate::denies('exam_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branches = Branch::where('status', 'active')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $courses  = Course::where('status', 'active')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $batches  = Batch::where('status', 'active')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $subjects = Subject::where('status', 'active')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $examTypes = $this->examTypes();

        $exam->load(['branch', 'course', 'batch', 'subject']);

        return view('admin.exams.edit', compact('exam', 'branches', 'courses', 'batches', 'subjects', 'examTypes'));
    }

    public function update(UpdateExamRequest $request, Exam $exam)
    {
        $exam->update($request->validated());

        return redirect()->route('admin.exams.index')->with('message', 'Exam updated successfully.');
    }

    public function destroy(Exam $exam)
    {
        abort_if(Gate::denies('exam_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $exam->delete();

        return back()->with('message', 'Exam deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('exam_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Exam::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function storeResults(StoreExamResultRequest $request, Exam $exam)
    {
        abort_if(Gate::denies('exam_result_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        foreach ($request->results as $resultData) {
            $marksObtained = (float) ($resultData['marks_obtained'] ?? 0);
            $totalMarks = (float) $exam->total_marks;

            $percentage = $totalMarks > 0 ? (($marksObtained / $totalMarks) * 100) : 0;

            $status = $resultData['result_status'];

            if ($status !== 'absent') {
                $status = $marksObtained >= $exam->passing_marks ? 'pass' : 'fail';
            }

            ExamResult::updateOrCreate(
                [
                    'exam_id'    => $exam->id,
                    'student_id' => $resultData['student_id'],
                ],
                [
                    'marks_obtained' => $marksObtained,
                    'total_marks'    => $exam->total_marks,
                    'percentage'     => round($percentage, 2),
                    'result_status'  => $status,
                    'remarks'        => $resultData['remarks'] ?? null,
                ]
            );
        }

        $this->generateRanks($exam);

        $exam->update(['status' => 'completed']);

        return back()->with('message', 'Exam results saved successfully.');
    }

    private function generateRanks(Exam $exam): void
    {
        $results = ExamResult::where('exam_id', $exam->id)
            ->where('result_status', '!=', 'absent')
            ->orderByDesc('marks_obtained')
            ->get();

        $rank = 1;

        foreach ($results as $result) {
            $result->update(['rank' => $rank]);
            $rank++;
        }
    }

    private function examTypes(): array
    {
        return [
            'Weekly Test'  => 'Weekly Test',
            'Monthly Test' => 'Monthly Test',
            'Unit Test'    => 'Unit Test',
            'Mock Test'    => 'Mock Test',
            'Final Test'   => 'Final Test',
            'Other'        => 'Other',
        ];
    }
}