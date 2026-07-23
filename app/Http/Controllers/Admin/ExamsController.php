<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExamRequest;
use App\Http\Requests\StoreExamResultRequest;
use App\Http\Requests\UpdateExamRequest;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Services\WhatsappService;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExamsController extends Controller
{
    use AppliesErpScope;
    public function index()
    {
        abort_if(Gate::denies('exam_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $exams = Exam::with(['branch', 'course', 'batch', 'subject']);

        if (auth()->user()->is_admin) {
            // Admin all exams
        } elseif ($this->isStudent()) {
            $student = Student::where('user_id', auth()->id())->first();

            if ($student) {
                $exams->where(function ($query) use ($student) {
                    $query->where(function ($q) use ($student) {
                        $q->whereNull('branch_id')->orWhere('branch_id', $student->branch_id);
                    })
                    ->where(function ($q) use ($student) {
                        $q->whereNull('course_id')->orWhere('course_id', $student->course_id);
                    })
                    ->where(function ($q) use ($student) {
                        $q->whereNull('batch_id')->orWhere('batch_id', $student->batch_id);
                    });
                });
            } else {
                $exams->whereRaw('1 = 0');
            }
        } elseif ($this->isTeacher()) {
            $assignmentIds = $this->getTeacherAssignmentIds();

            if ($assignmentIds['course_ids']->count() || $assignmentIds['subject_ids']->count() || $assignmentIds['batch_ids']->count()) {
                $exams->where(function ($query) use ($assignmentIds) {
                    $query
                        ->when($assignmentIds['course_ids']->count(), function ($q) use ($assignmentIds) {
                            $q->whereIn('course_id', $assignmentIds['course_ids']);
                        })
                        ->when($assignmentIds['subject_ids']->count(), function ($q) use ($assignmentIds) {
                            $q->orWhereIn('subject_id', $assignmentIds['subject_ids']);
                        })
                        ->when($assignmentIds['batch_ids']->count(), function ($q) use ($assignmentIds) {
                            $q->orWhereIn('batch_id', $assignmentIds['batch_ids']);
                        });
                });
            } else {
                $exams->whereRaw('1 = 0');
            }
        } else {
            $branchId = $this->getUserBranchId();

            $branchId ? $exams->where('branch_id', $branchId) : $exams->whereRaw('1 = 0');
        }

        $exams = $exams->latest()->get();

        return view('admin.exams.index', compact('exams'));
    }

    public function create()
    {
        abort_if(Gate::denies('exam_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branchId = $this->getUserBranchId();
        $assignmentIds = $this->getTeacherAssignmentIds();

        $branches = Branch::where('status', 'active')
            ->when(! auth()->user()->is_admin, fn ($q) => $branchId ? $q->where('id', $branchId) : $q->whereRaw('1 = 0'))
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $courses = Course::where('status', 'active')
            ->when(! auth()->user()->is_admin && ! $this->isTeacher(), fn ($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereRaw('1 = 0'))
            ->when($this->isTeacher(), fn ($q) => $assignmentIds['course_ids']->count() ? $q->whereIn('id', $assignmentIds['course_ids']) : $q->whereRaw('1 = 0'))
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $batches = Batch::where('status', 'active')
            ->when(! auth()->user()->is_admin && ! $this->isTeacher(), fn ($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereRaw('1 = 0'))
            ->when($this->isTeacher(), fn ($q) => $assignmentIds['batch_ids']->count() ? $q->whereIn('id', $assignmentIds['batch_ids']) : $q->whereRaw('1 = 0'))
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $subjects = Subject::where('status', 'active')
            ->when(! auth()->user()->is_admin && ! $this->isTeacher(), fn ($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereRaw('1 = 0'))
            ->when($this->isTeacher(), fn ($q) => $assignmentIds['subject_ids']->count() ? $q->whereIn('id', $assignmentIds['subject_ids']) : $q->whereRaw('1 = 0'))
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $examTypes = $this->examTypes();
        $batchesByBranch = $this->batchesByBranch();
        $coursesByBatch = $this->coursesByBatch();
        $subjectsByBatch = $this->subjectsByBatch();

        return view('admin.exams.create', compact('branches', 'courses', 'batches', 'subjects', 'examTypes', 'batchesByBranch', 'coursesByBatch', 'subjectsByBatch'));
    }

    public function store(StoreExamRequest $request, WhatsappService $whatsapp)
    {
        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validated();

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;

            if ($this->isTeacher()) {
                $this->validateTeacherExamData($data);
            } else {
                $this->validateBranchAcademicData($data, $branchId);
            }
        }

        $exam = Exam::create($data);

        $students = Student::query()
            ->when($exam->branch_id, fn ($q) => $q->where('branch_id', $exam->branch_id))
            ->when($exam->course_id, fn ($q) => $q->where('course_id', $exam->course_id))
            ->when($exam->batch_id, fn ($q) => $q->where(function ($qq) use ($exam) {
                $qq->where('batch_id', $exam->batch_id)
                    ->orWhereHas('studentBatches', fn ($bq) => $bq->where('batch_id', $exam->batch_id)->where('status', 'active'));
            }))
            ->get();

        foreach ($students as $student) {
            $whatsapp->sendStudentGuardianMessage(
                $student,
                'exam_schedule',
                'Exam scheduled: ' . $exam->title . ' on ' . ($exam->exam_date ? Carbon::parse($exam->exam_date)->format('d M Y') : '-')
            );
        }

        return redirect()->route('admin.exams.index')->with('message', 'Exam created successfully.');
    }

    public function show(Exam $exam)
    {
        abort_if(Gate::denies('exam_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkExamAccess($exam);

        $exam->load(['branch', 'course', 'batch', 'subject', 'results.student.user']);

        $students = Student::with(['user'])
            ->when($exam->branch_id, fn ($q) => $q->where('branch_id', $exam->branch_id))
            ->when($exam->course_id, fn ($q) => $q->where('course_id', $exam->course_id))
            ->when($exam->batch_id, fn ($q) => $q->where('batch_id', $exam->batch_id));

        if ($this->isStudent()) {
            $authStudent = Student::where('user_id', auth()->id())->first();
            $authStudent ? $students->where('id', $authStudent->id) : $students->whereRaw('1 = 0');

            // Student ko sirf apna hi result dikhe, poori class ka result report nahi.
            $exam->setRelation(
                'results',
                $authStudent ? $exam->results->where('student_id', $authStudent->id)->values() : $exam->results->take(0)
            );
        }

        if ($this->isParent()) {
            $parentStudentIds = Student::where('guardian_user_id', auth()->id())->pluck('id');
            $parentStudentIds->isNotEmpty() ? $students->whereIn('id', $parentStudentIds) : $students->whereRaw('1 = 0');

            // Parent ko sirf apne bacchon ka result dikhe.
            $exam->setRelation('results', $exam->results->whereIn('student_id', $parentStudentIds)->values());
        }

        if ($this->isTeacher()) {
            $batchIds = $this->getTeacherAssignmentIds()['batch_ids'];
            $batchIds->count() ? $students->whereIn('batch_id', $batchIds) : $students->whereRaw('1 = 0');
        }

        $students = $students->get();

        $existingResults = $exam->results->keyBy('student_id');

        return view('admin.exams.show', compact('exam', 'students', 'existingResults'));
    }

    public function edit(Exam $exam)
    {
        abort_if(Gate::denies('exam_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkExamAccess($exam);

        $branchId = $this->getUserBranchId();
        $assignmentIds = $this->getTeacherAssignmentIds();

        $branches = Branch::where('status', 'active')
            ->when(! auth()->user()->is_admin, fn ($q) => $branchId ? $q->where('id', $branchId) : $q->whereRaw('1 = 0'))
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $courses = Course::where('status', 'active')
            ->when(! auth()->user()->is_admin && ! $this->isTeacher(), fn ($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereRaw('1 = 0'))
            ->when($this->isTeacher(), fn ($q) => $assignmentIds['course_ids']->count() ? $q->whereIn('id', $assignmentIds['course_ids']) : $q->whereRaw('1 = 0'))
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $batches = Batch::where('status', 'active')
            ->when(! auth()->user()->is_admin && ! $this->isTeacher(), fn ($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereRaw('1 = 0'))
            ->when($this->isTeacher(), fn ($q) => $assignmentIds['batch_ids']->count() ? $q->whereIn('id', $assignmentIds['batch_ids']) : $q->whereRaw('1 = 0'))
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $subjects = Subject::where('status', 'active')
            ->when(! auth()->user()->is_admin && ! $this->isTeacher(), fn ($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereRaw('1 = 0'))
            ->when($this->isTeacher(), fn ($q) => $assignmentIds['subject_ids']->count() ? $q->whereIn('id', $assignmentIds['subject_ids']) : $q->whereRaw('1 = 0'))
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $examTypes = $this->examTypes();
        $batchesByBranch = $this->batchesByBranch();
        $coursesByBatch = $this->coursesByBatch();
        $subjectsByBatch = $this->subjectsByBatch();

        $exam->load(['branch', 'course', 'batch', 'subject']);

        return view('admin.exams.edit', compact('exam', 'branches', 'courses', 'batches', 'subjects', 'examTypes', 'batchesByBranch', 'coursesByBatch', 'subjectsByBatch'));
    }

    public function update(UpdateExamRequest $request, Exam $exam)
    {
        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkExamAccess($exam);

        $data = $request->validated();

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;

            if ($this->isTeacher()) {
                $this->validateTeacherExamData($data);
            } else {
                $this->validateBranchAcademicData($data, $branchId);
            }
        }

        $exam->update($data);

        return redirect()->route('admin.exams.index')->with('message', 'Exam updated successfully.');
    }

    public function destroy(Exam $exam)
    {
        abort_if(Gate::denies('exam_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkExamAccess($exam);

        $exam->delete();

        return back()->with('message', 'Exam deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('exam_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Exam::whereIn('id', request('ids'));

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
        }

        $query->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function storeResults(StoreExamResultRequest $request, Exam $exam)
    {
        abort_if(Gate::denies('exam_result_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkExamAccess($exam);

        foreach ($request->results as $resultData) {
            $student = Student::find($resultData['student_id'] ?? null);

            if (! $student) {
                continue;
            }

            if (! auth()->user()->is_admin) {
                if ($this->isTeacher()) {
                    $batchIds = $this->getTeacherAssignmentIds()['batch_ids'];

                    if (! $batchIds->contains($student->batch_id)) {
                        continue;
                    }
                } else {
                    $branchId = $this->getUserBranchId();

                    if (! $branchId || $student->branch_id != $branchId) {
                        continue;
                    }
                }
            }

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

    private function checkExamAccess(Exam $exam): void
    {
        if (auth()->user()->is_admin) {
            return;
        }

        if ($this->isStudent()) {
            $student = Student::where('user_id', auth()->id())->first();

            abort_if(
                ! $student ||
                ($exam->branch_id && $exam->branch_id != $student->branch_id) ||
                ($exam->course_id && $exam->course_id != $student->course_id) ||
                ($exam->batch_id && $exam->batch_id != $student->batch_id),
                Response::HTTP_FORBIDDEN,
                '403 Forbidden'
            );

            return;
        }

        if ($this->isTeacher()) {
            $assignmentIds = $this->getTeacherAssignmentIds();

            $allowed =
                ($exam->course_id && $assignmentIds['course_ids']->contains($exam->course_id)) ||
                ($exam->subject_id && $assignmentIds['subject_ids']->contains($exam->subject_id)) ||
                ($exam->batch_id && $assignmentIds['batch_ids']->contains($exam->batch_id));

            abort_if(! $allowed, Response::HTTP_FORBIDDEN, '403 Forbidden');

            return;
        }

        $branchId = $this->getUserBranchId();

        abort_if(! $branchId || $exam->branch_id != $branchId, Response::HTTP_FORBIDDEN, '403 Forbidden');
    }

    private function validateBranchAcademicData(array $data, $branchId): void
    {
        if (! empty($data['course_id'])) {
            abort_if(! Course::where('id', $data['course_id'])->where('branch_id', $branchId)->exists(), Response::HTTP_FORBIDDEN, 'Invalid course.');
        }

        if (! empty($data['batch_id'])) {
            abort_if(! Batch::where('id', $data['batch_id'])->where('branch_id', $branchId)->exists(), Response::HTTP_FORBIDDEN, 'Invalid batch.');
        }

        if (! empty($data['subject_id'])) {
            abort_if(! Subject::where('id', $data['subject_id'])->where('branch_id', $branchId)->exists(), Response::HTTP_FORBIDDEN, 'Invalid subject.');
        }
    }

    private function validateTeacherExamData(array $data): void
    {
        $ids = $this->getTeacherAssignmentIds();

        if (! empty($data['course_id'])) {
            abort_if(! $ids['course_ids']->contains((int) $data['course_id']), Response::HTTP_FORBIDDEN, 'Invalid course.');
        }

        if (! empty($data['batch_id'])) {
            abort_if(! $ids['batch_ids']->contains((int) $data['batch_id']), Response::HTTP_FORBIDDEN, 'Invalid batch.');
        }

        if (! empty($data['subject_id'])) {
            abort_if(! $ids['subject_ids']->contains((int) $data['subject_id']), Response::HTTP_FORBIDDEN, 'Invalid subject.');
        }
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

    private function getUserBranchId()
    {
        $user = auth()->user();

        if ($user->is_admin) {
            return null;
        }

        $managedBranch = Branch::where('manager_id', $user->id)->first();

        if ($managedBranch) {
            return $managedBranch->id;
        }

        $staff = Staff::where('user_id', $user->id)->first();

        if ($staff) {
            return $staff->branch_id;
        }

        $teacher = Teacher::where('user_id', $user->id)->first();

        if ($teacher) {
            return $teacher->branch_id;
        }

        $student = Student::where('user_id', $user->id)->first();

        if ($student) {
            return $student->branch_id;
        }

        return null;
    }

    private function getTeacherAssignmentIds(): array
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();

        if (! $teacher) {
            return [
                'course_ids'  => collect([]),
                'subject_ids' => collect([]),
                'batch_ids'   => collect([]),
            ];
        }

        $assignments = TeacherAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->get();

        return [
            'course_ids'  => $assignments->pluck('course_id')->filter()->unique()->values(),
            'subject_ids' => $assignments->pluck('subject_id')->filter()->unique()->values(),
            'batch_ids'   => $assignments->pluck('batch_id')->filter()->unique()->values(),
        ];
    }

    private function isTeacher(): bool
    {
        return auth()->user()->roles()->where('title', 'Teacher')->exists();
    }

    private function isStudent(): bool
    {
        return auth()->user()->roles()->where('title', 'Student')->exists();
    }

    private function isParent(): bool
    {
        return auth()->user()->roles()->where('title', 'Parent')->exists();
    }
}
