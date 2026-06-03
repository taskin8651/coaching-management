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

        $data = $this->validated($request);
        $this->validateStudentBatchAccess($data);
        $this->assertNoDuplicateAssignment($data);

        $studentBatch = StudentBatch::create($data);
        $studentBatch->load(['student', 'batch', 'subject']);
        $whatsapp->sendStudentGuardianMessage(
            $studentBatch->student,
            'batch_changed',
            'Batch assigned/changed: ' . ($studentBatch->batch->name ?? '-') . ($studentBatch->subject ? ' for ' . $studentBatch->subject->name : '')
        );

        return redirect()->route('admin.student-batches.index')->with('message', 'Student batch assigned successfully.');
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

        $data = $this->validated($request);
        $this->validateStudentBatchAccess($data);
        $this->assertNoDuplicateAssignment($data, $studentBatch->id);

        $studentBatch->update($data);
        $studentBatch->load(['student', 'batch', 'subject']);

        $whatsapp->sendStudentGuardianMessage(
            $studentBatch->student,
            'batch_changed',
            'Batch assignment updated: ' . ($studentBatch->batch->name ?? '-') . ($studentBatch->subject ? ' for ' . $studentBatch->subject->name : '')
        );

        return redirect()->route('admin.student-batches.index')->with('message', 'Student batch updated successfully.');
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
        return [
            'students' => $this->scopeStudentQuery(Student::with('user'))->get()->mapWithKeys(fn ($student) => [$student->id => $student->user->name ?? $student->student_code ?? ('Student #' . $student->id)])->prepend(trans('global.pleaseSelect'), ''),
            'batches' => $this->scopeBatchQuery(Batch::with('branch'))->get()->mapWithKeys(fn ($batch) => [$batch->id => $batch->name . ' - ' . ($batch->branch->name ?? '-')])->prepend(trans('global.pleaseSelect'), ''),
            'subjects' => $this->scopeBranchQuery(Subject::query())->pluck('name', 'id')->prepend('Optional', ''),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'batch_id' => ['required', 'exists:batches,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
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

    private function assertStudentBatchAccess(StudentBatch $studentBatch): void
    {
        abort_if(! $this->scopeStudentQuery(Student::query())->where('id', $studentBatch->student_id)->exists(), Response::HTTP_FORBIDDEN, '403 Forbidden');
    }
}
