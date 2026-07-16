<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Models\Batch;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Subject;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StudentAttendancesController extends Controller
{
    use AppliesErpScope;

    public function index(Request $request)
    {
        abort_if(Gate::denies('student_attendance_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $filters = $request->validate([
            'batch_id' => ['nullable', 'integer', 'exists:batches,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'status' => ['nullable', 'in:present,absent,late,half_day,leave'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $attendances = StudentAttendance::with(['student.user', 'batch', 'subject']);
        $scope = $this->erpScope();
        if ($scope['is_student'] && $scope['student_id']) {
            $attendances->where('student_id', $scope['student_id']);
        } elseif ($scope['is_parent'] && $scope['parent_student_ids']->isNotEmpty()) {
            $attendances->whereIn('student_id', $scope['parent_student_ids']);
        } elseif ($scope['is_teacher'] && $scope['teacher_id']) {
            $attendances->whereIn('batch_id', $this->teacherBatchIds($scope['teacher_id']));
        } elseif (! $scope['is_admin']) {
            $attendances->whereHas('student', fn ($q) => $this->scopeStudentQuery($q));
        }

        $attendances
            ->when(! empty($filters['batch_id']), fn ($q) => $q->where('batch_id', $filters['batch_id']))
            ->when(! empty($filters['subject_id']), fn ($q) => $q->where('subject_id', $filters['subject_id']))
            ->when(! empty($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->when(! empty($filters['date_from']), fn ($q) => $q->whereDate('attendance_date', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']), fn ($q) => $q->whereDate('attendance_date', '<=', $filters['date_to']));

        $attendances = $attendances->latest()->get();

        return view('admin.studentAttendances.index', compact('attendances', 'filters') + $this->filterOptions());
    }

    public function create()
    {
        abort_if(Gate::denies('student_attendance_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.studentAttendances.create', $this->formData());
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('student_attendance_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $this->validated($request);
        $this->validateAttendanceAccess($data);

        $attendance = StudentAttendance::updateOrCreate(
            [
                'unique_key' => StudentAttendance::makeUniqueKey($data['student_id'], $data['batch_id'], $data['subject_id'] ?? null, $data['attendance_date']),
            ],
            $data
        );

        return redirect()->route('admin.student-attendances.index')->with('message', 'Student attendance saved successfully.');
    }

    private function formData(): array
    {
        return [
            'students' => $this->scopeStudentQuery(Student::with('user'))->get()->mapWithKeys(fn ($student) => [$student->id => $student->user->name ?? $student->student_code ?? ('Student #' . $student->id)])->prepend(trans('global.pleaseSelect'), ''),
            'batches' => $this->scopeBatchQuery(Batch::query())->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), ''),
            'subjects' => $this->scopeBranchQuery(Subject::query())->pluck('name', 'id')->prepend('Optional', ''),
        ];
    }

    private function filterOptions(): array
    {
        return [
            'batches' => $this->scopeBatchQuery(Batch::query())->pluck('name', 'id')->prepend('All Batches', ''),
            'subjects' => $this->scopeBranchQuery(Subject::query())->pluck('name', 'id')->prepend('All Subjects', ''),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'batch_id' => ['required', 'exists:batches,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'attendance_date' => ['required', 'date'],
            'scheduled_start_time' => ['nullable', 'date_format:H:i'],
            'scheduled_end_time' => ['nullable', 'date_format:H:i'],
            'actual_in_time' => ['nullable', 'date_format:H:i'],
            'actual_out_time' => ['nullable', 'date_format:H:i'],
            'status' => ['required', 'in:present,absent,late,half_day'],
            'source' => ['required', 'in:manual,biometric'],
            'remarks' => ['nullable', 'string'],
        ]);
    }

    private function validateAttendanceAccess(array $data): void
    {
        abort_if(! $this->scopeStudentQuery(Student::query())->where('id', $data['student_id'])->exists(), Response::HTTP_FORBIDDEN, 'Invalid student.');
        abort_if(! $this->scopeBatchQuery(Batch::query())->where('id', $data['batch_id'])->exists(), Response::HTTP_FORBIDDEN, 'Invalid batch.');

        if (! empty($data['subject_id'])) {
            abort_if(! $this->scopeBranchQuery(Subject::query())->where('id', $data['subject_id'])->exists(), Response::HTTP_FORBIDDEN, 'Invalid subject.');
        }
    }
}
