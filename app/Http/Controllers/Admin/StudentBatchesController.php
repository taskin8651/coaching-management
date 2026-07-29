<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Student;
use App\Models\StudentBatch;
use App\Models\Subject;
use App\Services\WhatsappService;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class StudentBatchesController extends Controller
{
    use AppliesErpScope;

    public function index()
    {
        abort_if(Gate::denies('student_batch_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $studentBatches = StudentBatch::with(['student.user', 'batch.branch', 'subject']);
        $scope = $this->erpScope();
        if (! $scope['is_admin']) {
            $studentBatches->whereHas('student', fn ($q) => $this->scopeStudentQuery($q));
        }
        $studentBatches = $studentBatches->latest()->get();

        return view('admin.studentBatches.index', compact('studentBatches'));
    }

    public function create()
    {
        abort_if(Gate::denies('student_batch_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.studentBatches.create', $this->formData());
    }

    public function matrix(Request $request)
    {
        abort_if(Gate::denies('student_batch_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validate([
            'batch_ids' => ['required', 'array', 'min:1'],
            'batch_ids.*' => ['integer', 'exists:batches,id'],
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
        ]);

        $batchIds = array_values(array_unique($data['batch_ids']));

        $batches = $this->authorizedBatchQuery()
            ->with(['subjects' => fn ($query) => $query->where('status', 'active')->orderBy('name')])
            ->whereIn('id', $batchIds)
            ->get();

        abort_if($batches->count() !== count($batchIds), Response::HTTP_FORBIDDEN, 'Invalid batch selected.');

        $studentIdsByBatch = [];
        $allStudents = collect();

        foreach ($batches as $batch) {
            $eligible = $this->eligibleStudentsForBatch($batch)
                ->with(['user:id,name,email'])
                ->get(['id', 'user_id', 'branch_id', 'course_id', 'batch_id', 'student_code', 'phone']);

            $studentIdsByBatch[$batch->id] = $eligible->pluck('id')->map(fn ($id) => (int) $id)->all();
            $allStudents = $allStudents->merge($eligible);
        }

        if (! empty($data['student_id'])) {
            // "Manage" from the index screen edits one student at a time — scope the matrix
            // down to just them instead of showing every other student eligible for the batch.
            $onlyStudentId = (int) $data['student_id'];

            abort_if(
                ! $this->scopeStudentQuery(Student::query())->where('id', $onlyStudentId)->exists(),
                Response::HTTP_FORBIDDEN,
                '403 Forbidden'
            );

            if (! $allStudents->contains('id', $onlyStudentId)) {
                $managedStudent = Student::with(['user:id,name,email'])
                    ->find($onlyStudentId, ['id', 'user_id', 'branch_id', 'course_id', 'batch_id', 'student_code', 'phone']);

                if ($managedStudent) {
                    $allStudents->push($managedStudent);
                }
            }

            $allStudents = $allStudents->where('id', $onlyStudentId)->values();

            foreach ($studentIdsByBatch as $batchId => $ids) {
                $studentIdsByBatch[$batchId] = in_array($onlyStudentId, $ids, true) ? [$onlyStudentId] : [];
            }
        }

        $allStudents = $allStudents->unique('id')->sortBy([['student_code', 'asc'], ['id', 'asc']])->values();
        $allStudentIds = $allStudents->pluck('id');
        $allSubjectIds = $batches->flatMap(fn ($batch) => $batch->subjects->pluck('id'))->unique()->values();

        $assignments = StudentBatch::query()
            ->whereIn('batch_id', $batchIds)
            ->whereIn('student_id', $allStudentIds)
            ->whereIn('subject_id', $allSubjectIds)
            ->where('status', 'active')
            ->get(['student_id', 'batch_id', 'subject_id'])
            ->groupBy('student_id')
            ->map(fn ($rows) => $rows->groupBy('batch_id')
                ->map(fn ($r) => $r->pluck('subject_id')->map(fn ($id) => (int) $id)->values()))
            ->toArray();

        return response()->json([
            'batches' => $batches->map(fn ($batch) => [
                'id' => $batch->id,
                'name' => $batch->name,
                'branch_id' => $batch->branch_id,
                'course_id' => $batch->course_id,
                'student_ids' => $studentIdsByBatch[$batch->id],
                'subjects' => $batch->subjects->map(fn ($subject) => [
                    'id' => $subject->id,
                    'name' => $subject->name,
                ])->values(),
            ])->values(),
            'students' => $allStudents->map(fn ($student) => [
                'id' => $student->id,
                'name' => $student->user->name ?? $student->student_code ?? 'Student #'.$student->id,
                'code' => $student->student_code,
                'phone' => $student->phone,
            ])->values(),
            'assignments' => $assignments,
        ]);
    }

    public function store(Request $request, WhatsappService $whatsapp)
    {
        abort_if(Gate::denies('student_batch_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $this->validatedMatrix($request);
        $result = $this->syncMatrixAssignments($data);

        $message = "Student subject assignments saved. {$result['created']} added, {$result['deleted']} removed.";

        return redirect()->route('admin.student-batches.index')->with('message', $message);
    }

    public function edit(StudentBatch $studentBatch)
    {
        abort_if(Gate::denies('student_batch_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->assertStudentBatchAccess($studentBatch);

        $studentBatch->load('student.user', 'batch');
        $managedStudentId = $studentBatch->student_id;
        $selectedCourseId = $studentBatch->batch->course_id ?? null;

        return view('admin.studentBatches.edit', $this->formData() + compact('studentBatch', 'managedStudentId', 'selectedCourseId'));
    }

    public function update(Request $request, StudentBatch $studentBatch, WhatsappService $whatsapp)
    {
        abort_if(Gate::denies('student_batch_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->assertStudentBatchAccess($studentBatch);

        $data = $this->validatedMatrix($request);
        $result = $this->syncMatrixAssignments($data);

        $message = "Student subject assignments updated. {$result['created']} added, {$result['deleted']} removed.";

        return redirect()->route('admin.student-batches.index')->with('message', $message);
    }

    public function destroy(StudentBatch $studentBatch)
    {
        abort_if(Gate::denies('student_batch_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->assertStudentBatchAccess($studentBatch);

        $studentBatch->delete();

        return back()->with('message', 'Student batch assignment deleted successfully.');
    }

    private function formData(): array
    {
        $batches = $this->scopeBatchQuery(Batch::with(['branch', 'subjects']))->get();

        return [
            'batches' => $batches->mapWithKeys(fn ($batch) => [$batch->id => $batch->name . ' - ' . ($batch->branch->name ?? '-')])->prepend(trans('global.pleaseSelect'), ''),
            'courses' => $this->scopeBranchQuery(Course::where('status', 'active'))
                ->orderBy('name')
                ->pluck('name', 'id')
                ->prepend(trans('global.pleaseSelect'), ''),
            'batchesByCourse' => $this->batchesByCourse(),
        ];
    }

    /**
     * Active batches grouped by course_id, for the Course -> Batches checkbox-grid cascade
     * on the Assign/Manage Student Batches screen.
     * Shape: { course_id: [{id, name}, ...] }
     */
    private function batchesByCourse(): array
    {
        return $this->scopeBatchQuery(Batch::where('status', 'active'))
            ->orderBy('name')
            ->get(['id', 'name', 'course_id'])
            ->filter(fn ($batch) => $batch->course_id)
            ->groupBy('course_id')
            ->map(fn ($batches) => $batches->map(fn ($batch) => [
                'id' => $batch->id,
                'name' => $batch->name,
            ])->values())
            ->toArray();
    }

    private function validatedMatrix(Request $request): array
    {
        return $request->validate([
            'batch_ids' => ['required', 'array', 'min:1'],
            'batch_ids.*' => ['integer', 'exists:batches,id'],
            'assignments' => ['nullable', 'array'],
            'assignments.*' => ['nullable', 'array'],
            'assignments.*.*' => ['nullable', 'array'],
            'assignments.*.*.*' => ['integer', 'exists:subjects,id'],
            'status' => ['required', 'in:active,inactive'],
            'only_student_id' => ['nullable', 'integer', 'exists:students,id'],
        ]);
    }

    private function syncMatrixAssignments(array $data): array
    {
        $batchIds = array_values(array_unique($data['batch_ids']));

        $batches = $this->authorizedBatchQuery()
            ->with('subjects:id')
            ->whereIn('id', $batchIds)
            ->get();

        abort_if($batches->count() !== count($batchIds), Response::HTTP_FORBIDDEN, 'Invalid batch selected.');

        $created = 0;
        $deleted = 0;

        DB::transaction(function () use ($batches, $data, &$created, &$deleted) {
            foreach ($batches as $batch) {
                $result = $this->syncBatchAssignments($batch, $data, $data['only_student_id'] ?? null);
                $created += $result['created'];
                $deleted += $result['deleted'];
            }
        });

        return compact('created', 'deleted');
    }

    private function syncBatchAssignments(Batch $batch, array $data, ?int $onlyStudentId = null): array
    {
        $students = $this->eligibleStudentsForBatch($batch)->get(['id']);
        $eligibleStudentIds = $students->pluck('id')->map(fn ($id) => (int) $id)->values();

        if ($onlyStudentId !== null) {
            // "Manage" edits one student at a time — only touch that student's assignments for
            // this batch, so the rest of the batch's roster is left completely untouched.
            $eligibleStudentIds = $eligibleStudentIds->filter(fn ($id) => $id === $onlyStudentId)->values();
        }

        $eligibleStudentSet = $eligibleStudentIds->flip();
        $batchSubjectIds = $batch->subjects->pluck('id')->map(fn ($id) => (int) $id)->values();
        $batchSubjectSet = $batchSubjectIds->flip();

        $requested = collect($data['assignments'] ?? [])->map(function ($byBatch) use ($batch) {
            $byBatch = collect($byBatch);

            return $byBatch->get((string) $batch->id, $byBatch->get($batch->id, []));
        });

        foreach ($requested as $studentId => $subjectIds) {
            if (empty($subjectIds)) {
                continue;
            }

            abort_if(! $eligibleStudentSet->has((int) $studentId), Response::HTTP_FORBIDDEN, 'Invalid student selected.');

            foreach ((array) $subjectIds as $subjectId) {
                abort_if(! $batchSubjectSet->has((int) $subjectId), Response::HTTP_FORBIDDEN, 'Selected subject is not linked with selected batch.');
            }
        }

        $existingRows = StudentBatch::where('batch_id', $batch->id)
            ->whereIn('student_id', $eligibleStudentIds)
            ->whereIn('subject_id', $batchSubjectIds)
            ->get(['id', 'student_id', 'subject_id', 'unique_key']);

        $existingKeys = $existingRows
            ->mapWithKeys(fn ($row) => [StudentBatch::makeUniqueKey($row->student_id, $batch->id, $row->subject_id) => $row])
            ->all();

        $desiredKeys = [];

        foreach ($eligibleStudentIds as $studentId) {
            $subjectIds = collect($requested->get((string) $studentId, $requested->get((int) $studentId, [])))
                ->map(fn ($id) => (int) $id)
                ->intersect($batchSubjectIds)
                ->unique()
                ->values();

            foreach ($subjectIds as $subjectId) {
                $key = StudentBatch::makeUniqueKey($studentId, $batch->id, $subjectId);
                $desiredKeys[$key] = [
                    'student_id' => $studentId,
                    'batch_id' => $batch->id,
                    'subject_id' => $subjectId,
                    'status' => $data['status'],
                ];
            }
        }

        $deleteIds = [];

        foreach ($existingKeys as $key => $row) {
            if (! isset($desiredKeys[$key])) {
                $deleteIds[] = $row->id;
            }
        }

        $created = 0;
        $deleted = 0;

        if ($deleteIds) {
            $deleted = count($deleteIds);
            StudentBatch::whereIn('id', $deleteIds)->delete();
        }

        foreach ($desiredKeys as $key => $row) {
            if (isset($existingKeys[$key])) {
                StudentBatch::where('id', $existingKeys[$key]->id)->update(['status' => $data['status']]);
                continue;
            }

            StudentBatch::create($row);
            $created++;
        }

        return compact('created', 'deleted');
    }

    private function assertStudentBatchAccess(StudentBatch $studentBatch): void
    {
        abort_if(! $this->scopeStudentQuery(Student::query())->where('id', $studentBatch->student_id)->exists(), Response::HTTP_FORBIDDEN, '403 Forbidden');
    }

    private function authorizedBatchQuery()
    {
        return $this->scopeBatchQuery(Batch::query());
    }

    private function eligibleStudentsForBatch(Batch $batch)
    {
        return $this->scopeStudentQuery(Student::query())
            ->where(function ($query) use ($batch) {
                $query->where('batch_id', $batch->id)
                    ->orWhereHas('studentBatches', fn ($q) => $q->where('batch_id', $batch->id)->where('status', 'active'));
            });
    }
}
