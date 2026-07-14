<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Models\Batch;
use App\Models\Branch;
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
            'batch_id' => ['required', 'integer', 'exists:batches,id'],
        ]);

        $batch = $this->authorizedBatchQuery()
            ->with(['subjects' => fn ($query) => $query->where('status', 'active')->orderBy('name')])
            ->findOrFail($data['batch_id']);

        $students = $this->eligibleStudentsForBatch($batch)
            ->with(['user:id,name,email'])
            ->orderBy('student_code')
            ->orderBy('id')
            ->get(['id', 'user_id', 'branch_id', 'course_id', 'batch_id', 'student_code', 'phone']);

        $studentIds = $students->pluck('id');
        $subjectIds = $batch->subjects->pluck('id');

        $assignments = StudentBatch::query()
            ->where('batch_id', $batch->id)
            ->whereIn('student_id', $studentIds)
            ->whereIn('subject_id', $subjectIds)
            ->where('status', 'active')
            ->get(['student_id', 'subject_id'])
            ->groupBy('student_id')
            ->map(fn ($rows) => $rows->pluck('subject_id')->map(fn ($id) => (int) $id)->values())
            ->toArray();

        return response()->json([
            'batch' => [
                'id' => $batch->id,
                'name' => $batch->name,
                'branch_id' => $batch->branch_id,
                'course_id' => $batch->course_id,
            ],
            'students' => $students->map(fn ($student) => [
                'id' => $student->id,
                'name' => $student->user->name ?? $student->student_code ?? 'Student #'.$student->id,
                'code' => $student->student_code,
                'phone' => $student->phone,
            ])->values(),
            'subjects' => $batch->subjects->map(fn ($subject) => [
                'id' => $subject->id,
                'name' => $subject->name,
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

        return view('admin.studentBatches.edit', $this->formData() + compact('studentBatch'));
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
        ];
    }

    private function validatedMatrix(Request $request): array
    {
        return $request->validate([
            'batch_id' => ['required', 'exists:batches,id'],
            'assignments' => ['nullable', 'array'],
            'assignments.*' => ['nullable', 'array'],
            'assignments.*.*' => ['integer', 'exists:subjects,id'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }

    private function syncMatrixAssignments(array $data): array
    {
        $batch = $this->authorizedBatchQuery()
            ->with('subjects:id')
            ->findOrFail($data['batch_id']);

        $students = $this->eligibleStudentsForBatch($batch)->get(['id']);
        $eligibleStudentIds = $students->pluck('id')->map(fn ($id) => (int) $id)->values();
        $eligibleStudentSet = $eligibleStudentIds->flip();
        $batchSubjectIds = $batch->subjects->pluck('id')->map(fn ($id) => (int) $id)->values();
        $batchSubjectSet = $batchSubjectIds->flip();
        $requested = collect($data['assignments'] ?? []);

        foreach ($requested as $studentId => $subjectIds) {
            abort_if(! $eligibleStudentSet->has((int) $studentId), Response::HTTP_FORBIDDEN, 'Invalid student selected.');

            foreach ((array) $subjectIds as $subjectId) {
                abort_if(! $batchSubjectSet->has((int) $subjectId), Response::HTTP_FORBIDDEN, 'Selected subject is not linked with selected batch.');
            }
        }

        $created = 0;
        $deleted = 0;

        DB::transaction(function () use ($batch, $eligibleStudentIds, $batchSubjectIds, $requested, $data, &$created, &$deleted) {
            $existingRows = StudentBatch::where('batch_id', $batch->id)
                ->whereIn('student_id', $eligibleStudentIds)
                ->whereIn('subject_id', $batchSubjectIds)
                ->get(['id', 'student_id', 'subject_id', 'unique_key']);

            $existingKeys = $existingRows
                ->mapWithKeys(fn ($row) => [StudentBatch::makeUniqueKey($row->student_id, $row->batch_id ?? $batch->id, $row->subject_id) => $row])
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
        });

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
            ->where('branch_id', $batch->branch_id)
            ->when($batch->course_id, fn ($query) => $query->where('course_id', $batch->course_id));
    }
}
