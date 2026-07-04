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

    public function store(Request $request, WhatsappService $whatsapp)
    {
        abort_if(Gate::denies('student_batch_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $this->validatedForStore($request);
        $this->validateBulkStudentBatchAccess($data);

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($data, $whatsapp, &$created, &$skipped) {
            foreach ($data['student_ids'] as $studentId) {
                foreach ($this->subjectIdsForCreate($data) as $subjectId) {
                    $row = [
                        'student_id' => $studentId,
                        'batch_id' => $data['batch_id'],
                        'subject_id' => $subjectId ?: null,
                        'start_date' => $data['start_date'] ?? null,
                        'end_date' => $data['end_date'] ?? null,
                        'status' => $data['status'],
                    ];

                    if ($this->duplicateAssignmentExists($row)) {
                        $skipped++;
                        continue;
                    }

                    $studentBatch = StudentBatch::create($row);
                    $studentBatch->load(['student', 'batch', 'subject']);

                    $whatsapp->sendStudentGuardianMessage(
                        $studentBatch->student,
                        'batch_changed',
                        'Batch assigned/changed: ' . ($studentBatch->batch->name ?? '-') . ($studentBatch->subject ? ' for ' . $studentBatch->subject->name : '')
                    );

                    $created++;
                }
            }
        });

        $message = $created . ' student batch assignment(s) saved successfully.';
        if ($skipped) {
            $message .= ' ' . $skipped . ' duplicate assignment(s) skipped.';
        }

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

        $data = $this->validatedForUpdate($request);
        $this->validateUpdateStudentBatchAccess($data);

        $subjectIds = $this->subjectIdsForCreate($data);
        $firstSubjectId = array_shift($subjectIds);

        $primaryData = [
            'student_id' => $data['student_id'],
            'batch_id' => $data['batch_id'],
            'subject_id' => $firstSubjectId ?: null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'status' => $data['status'],
        ];

        $this->assertNoDuplicateAssignment($primaryData, $studentBatch->id);

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($studentBatch, $primaryData, $subjectIds, $data, &$created, &$skipped) {
            $studentBatch->update($primaryData);

            foreach ($subjectIds as $subjectId) {
                $row = [
                    'student_id' => $data['student_id'],
                    'batch_id' => $data['batch_id'],
                    'subject_id' => $subjectId ?: null,
                    'start_date' => $data['start_date'] ?? null,
                    'end_date' => $data['end_date'] ?? null,
                    'status' => $data['status'],
                ];

                if ($this->duplicateAssignmentExists($row)) {
                    $skipped++;
                    continue;
                }

                StudentBatch::create($row);
                $created++;
            }
        });

        $studentBatch->load(['student', 'batch', 'subject']);

        $whatsapp->sendStudentGuardianMessage(
            $studentBatch->student,
            'batch_changed',
            'Batch assignment updated: ' . ($studentBatch->batch->name ?? '-') . ($studentBatch->subject ? ' for ' . $studentBatch->subject->name : '')
        );

        $message = 'Student batch updated successfully.';
        if ($created) {
            $message .= ' ' . $created . ' additional subject assignment(s) added.';
        }
        if ($skipped) {
            $message .= ' ' . $skipped . ' duplicate assignment(s) skipped.';
        }

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
        $batchSubjects = $batches->mapWithKeys(function ($batch) {
            return [
                $batch->id => $batch->subjects
                    ->map(fn ($subject) => ['id' => $subject->id, 'name' => $subject->name])
                    ->values(),
            ];
        });

        return [
            'students' => $this->scopeStudentQuery(Student::with('user'))->get()->mapWithKeys(fn ($student) => [$student->id => $student->user->name ?? $student->student_code ?? ('Student #' . $student->id)])->prepend(trans('global.pleaseSelect'), ''),
            'batches' => $batches->mapWithKeys(fn ($batch) => [$batch->id => $batch->name . ' - ' . ($batch->branch->name ?? '-')])->prepend(trans('global.pleaseSelect'), ''),
            'subjects' => $this->scopeBranchQuery(Subject::query())->pluck('name', 'id')->prepend('Optional', ''),
            'batchSubjects' => $batchSubjects,
        ];
    }

    private function validatedForStore(Request $request): array
    {
        return $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
            'batch_id' => ['required', 'exists:batches,id'],
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }

    private function validatedForUpdate(Request $request): array
    {
        return $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'batch_id' => ['required', 'exists:batches,id'],
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }

    private function validateStudentBatchAccess(array $data): void
    {
        abort_if(! $this->scopeStudentQuery(Student::query())->where('id', $data['student_id'])->exists(), Response::HTTP_FORBIDDEN, 'Invalid student.');
        abort_if(! $this->scopeBatchQuery(Batch::query())->where('id', $data['batch_id'])->exists(), Response::HTTP_FORBIDDEN, 'Invalid batch.');

        if (! empty($data['subject_id'])) {
            abort_if(! $this->scopeBranchQuery(Subject::query())->where('id', $data['subject_id'])->exists(), Response::HTTP_FORBIDDEN, 'Invalid subject.');
            abort_if(! Batch::where('id', $data['batch_id'])->whereHas('subjects', fn ($q) => $q->where('subjects.id', $data['subject_id']))->exists(), Response::HTTP_FORBIDDEN, 'Selected subject is not linked with selected batch.');
        }
    }

    private function validateBulkStudentBatchAccess(array $data): void
    {
        $studentCount = $this->scopeStudentQuery(Student::query())
            ->whereIn('id', $data['student_ids'])
            ->count();

        abort_if($studentCount !== count(array_unique($data['student_ids'])), Response::HTTP_FORBIDDEN, 'Invalid student selected.');
        abort_if(! $this->scopeBatchQuery(Batch::query())->where('id', $data['batch_id'])->exists(), Response::HTTP_FORBIDDEN, 'Invalid batch.');

        $subjectIds = array_filter($data['subject_ids'] ?? []);

        if ($subjectIds) {
            $linkedCount = Batch::where('id', $data['batch_id'])
                ->firstOrFail()
                ->subjects()
                ->whereIn('subjects.id', $subjectIds)
                ->count();

            abort_if($linkedCount !== count(array_unique($subjectIds)), Response::HTTP_FORBIDDEN, 'Selected subject is not linked with selected batch.');
        }
    }

    private function validateUpdateStudentBatchAccess(array $data): void
    {
        abort_if(! $this->scopeStudentQuery(Student::query())->where('id', $data['student_id'])->exists(), Response::HTTP_FORBIDDEN, 'Invalid student.');
        abort_if(! $this->scopeBatchQuery(Batch::query())->where('id', $data['batch_id'])->exists(), Response::HTTP_FORBIDDEN, 'Invalid batch.');

        $subjectIds = array_filter($data['subject_ids'] ?? []);

        if ($subjectIds) {
            $linkedCount = Batch::where('id', $data['batch_id'])
                ->firstOrFail()
                ->subjects()
                ->whereIn('subjects.id', $subjectIds)
                ->count();

            abort_if($linkedCount !== count(array_unique($subjectIds)), Response::HTTP_FORBIDDEN, 'Selected subject is not linked with selected batch.');
        }
    }

    private function assertNoDuplicateAssignment(array $data, ?int $ignoreId = null): void
    {
        $query = StudentBatch::where('unique_key', StudentBatch::makeUniqueKey($data['student_id'], $data['batch_id'], $data['subject_id'] ?? null));

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        abort_if($query->exists(), Response::HTTP_UNPROCESSABLE_ENTITY, 'Student is already assigned to this batch/subject.');
    }

    private function duplicateAssignmentExists(array $data): bool
    {
        return StudentBatch::where('unique_key', StudentBatch::makeUniqueKey($data['student_id'], $data['batch_id'], $data['subject_id'] ?? null))->exists();
    }

    private function subjectIdsForCreate(array $data): array
    {
        $subjectIds = array_values(array_filter($data['subject_ids'] ?? []));

        return $subjectIds ?: [null];
    }

    private function assertStudentBatchAccess(StudentBatch $studentBatch): void
    {
        abort_if(! $this->scopeStudentQuery(Student::query())->where('id', $studentBatch->student_id)->exists(), Response::HTTP_FORBIDDEN, '403 Forbidden');
    }
}
