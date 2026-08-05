<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\FacultyLogBook;
use App\Models\StaffAttendance;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Services\SalaryCalculationService;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FacultyLogBooksController extends Controller
{
    use AppliesErpScope;

    private const TIMEZONE = 'Asia/Kolkata';

    public function index()
    {
        abort_if(Gate::denies('faculty_log_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $logs = FacultyLogBook::with(['teacher.user', 'batch', 'subject', 'approvedBy']);
        $scope = $this->erpScope();
        if ($scope['is_teacher'] && $scope['teacher_id']) {
            $logs->where('teacher_id', $scope['teacher_id']);
        } elseif (! $scope['is_admin']) {
            $this->scopeBranchQuery($logs);
        }

        $logs = $logs->latest()->get();
        $this->flagVerifiedActualTimes($logs);

        return view('admin.facultyLogBooks.index', ['logs' => $logs]);
    }

    public function create()
    {
        abort_if(Gate::denies('faculty_log_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $teacher = $this->loggedInTeacher();

        return view('admin.facultyLogBooks.create', $this->formData($teacher));
    }

    public function timetable(Request $request)
    {
        abort_if(Gate::denies('faculty_log_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $teacher = $this->loggedInTeacher();
        $selection = $request->validate([
            'batch_id' => ['required', 'exists:batches,id'],
            'lecture_date' => ['required', 'date'],
        ]);

        $timetable = $this->timetableFor($teacher, $selection['batch_id'], $selection['lecture_date']);
        abort_if(! $timetable, Response::HTTP_UNPROCESSABLE_ENTITY, 'Timetable not found for selected batch and date.');

        return response()->json($this->timetablePayload($teacher, $timetable->load(['branch', 'subject'])));
    }

    public function store(Request $request, SalaryCalculationService $salaryService)
    {
        abort_if(Gate::denies('faculty_log_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $teacher = $this->loggedInTeacher();
        $this->mergeTrustedTimetableData($request, $teacher);
        $validated = $this->validated($request);

        if ($actual = $this->attendanceActualTimes($teacher->id, $validated['batch_id'], $validated['lecture_date'])) {
            $validated = array_merge($validated, $actual);
        }

        $data = $this->prepare($validated, $salaryService);

        abort_if(FacultyLogBook::where('unique_key', FacultyLogBook::makeUniqueKey($data))->exists(), Response::HTTP_UNPROCESSABLE_ENTITY, 'Faculty log already exists for this teacher, batch and timetable slot.');
        FacultyLogBook::create($data);

        return redirect()->route('admin.faculty-log-books.index')->with('message', 'Faculty log saved successfully.');
    }

    public function edit(FacultyLogBook $facultyLogBook)
    {
        abort_if(Gate::denies('faculty_log_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $teacher = $this->loggedInTeacher();
        $this->assertTeacherCanEdit($facultyLogBook, $teacher);

        $facultyLogBook->load('batch', 'branch', 'subject');

        return view('admin.facultyLogBooks.edit', $this->formData($teacher) + compact('facultyLogBook'));
    }

    public function update(Request $request, FacultyLogBook $facultyLogBook, SalaryCalculationService $salaryService)
    {
        abort_if(Gate::denies('faculty_log_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $teacher = $this->loggedInTeacher();
        $this->assertTeacherCanEdit($facultyLogBook, $teacher);
        $this->assertSameLectureSession($request, $facultyLogBook);

        $this->mergeTrustedTimetableData($request, $teacher);
        $validated = $this->validated($request);

        if ($actual = $this->attendanceActualTimes($teacher->id, $validated['batch_id'], $validated['lecture_date'])) {
            $validated = array_merge($validated, $actual);
        }

        $data = $this->prepare($validated, $salaryService);
        abort_if(FacultyLogBook::where('unique_key', FacultyLogBook::makeUniqueKey($data))->where('id', '!=', $facultyLogBook->id)->exists(), Response::HTTP_UNPROCESSABLE_ENTITY, 'Faculty log already exists for this teacher, batch and timetable slot.');

        $facultyLogBook->update($data);

        return redirect()->route('admin.faculty-log-books.index')->with('message', 'Faculty log updated successfully.');
    }

    public function approve(FacultyLogBook $facultyLogBook, SalaryCalculationService $salaryService)
    {
        abort_if(Gate::denies('faculty_log_approve'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->assertBranchAccess($facultyLogBook);

        $data = [
            'approval_status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(self::TIMEZONE),
        ];

        // Attendance may have been completed after the log was first submitted — re-sync the
        // actual times (and the salary minutes they drive) from real punch data before this
        // log's numbers are locked in by approval.
        if ($actual = $this->attendanceActualTimes($facultyLogBook->teacher_id, $facultyLogBook->batch_id, $facultyLogBook->lecture_date)) {
            $minutes = $salaryService->payableMinutes(
                $facultyLogBook->scheduled_start_time,
                $facultyLogBook->scheduled_end_time,
                $actual['actual_start_time'],
                $actual['actual_end_time']
            );

            $data += $actual + [
                'scheduled_minutes' => $minutes['scheduled_minutes'],
                'salary_minutes' => $minutes['salary_minutes'],
            ];
        }

        $data['is_salary_eligible'] = ($data['salary_minutes'] ?? $facultyLogBook->salary_minutes) > 0;

        $facultyLogBook->update($data);

        return back()->with('message', 'Faculty log approved successfully.');
    }

    /**
     * Annotate each log with `is_actual_verified` (true = actual times came from a completed
     * attendance punch, false = still the scheduled-time estimate) so the list can show which
     * logs still need attendance to catch up before their payable minutes can be trusted.
     */
    private function flagVerifiedActualTimes(\Illuminate\Support\Collection $logs): void
    {
        if ($logs->isEmpty()) {
            return;
        }

        $verifiedKeys = StaffAttendance::whereIn('teacher_id', $logs->pluck('teacher_id')->unique())
            ->whereIn('batch_id', $logs->pluck('batch_id')->unique())
            ->whereNotNull('first_in_time')
            ->whereNotNull('last_out_time')
            ->get(['teacher_id', 'batch_id', 'attendance_date'])
            ->map(fn ($row) => $row->teacher_id . ':' . $row->batch_id . ':' . $row->attendance_date)
            ->flip();

        $logs->each(function (FacultyLogBook $log) use ($verifiedKeys) {
            $log->is_actual_verified = $verifiedKeys->has($log->teacher_id . ':' . $log->batch_id . ':' . $log->lecture_date);
        });
    }

    private function loggedInTeacher(): Teacher
    {
        abort_if(! auth()->user()->roles()->where('title', 'Teacher')->exists(), Response::HTTP_FORBIDDEN, 'Only teacher can create or edit faculty log book.');

        $teacher = Teacher::where('user_id', auth()->id())->first();
        abort_if(! $teacher, Response::HTTP_FORBIDDEN, 'Teacher profile not found for this login user.');

        return $teacher;
    }

    private function formData(Teacher $teacher): array
    {
        return [
            'batches' => $this->teacherBatchIds($teacher->id)->isNotEmpty()
                ? Batch::whereIn('id', $this->teacherBatchIds($teacher->id))->pluck('name', 'id')
                : collect(),
        ];
    }

    private function mergeTrustedTimetableData(Request $request, Teacher $teacher): void
    {
        $selection = $request->validate([
            'batch_id' => ['required', 'exists:batches,id'],
            'lecture_date' => ['required', 'date'],
        ]);
        $this->assertSubmissionWindow($selection['lecture_date']);

        $timetable = $this->timetableFor($teacher, $selection['batch_id'], $selection['lecture_date']);
        abort_if(! $timetable, Response::HTTP_UNPROCESSABLE_ENTITY, 'Timetable not found for selected batch and date.');

        $request->merge($this->timetablePayload($teacher, $timetable) + [
            'lecture_date' => Carbon::parse($selection['lecture_date'], self::TIMEZONE)->toDateString(),
            'log_status' => 'submitted',
            'approval_status' => 'pending',
            'is_salary_eligible' => false,
        ]);
    }

    private function timetableFor(Teacher $teacher, int $batchId, string $lectureDate): ?Timetable
    {
        $date = Carbon::parse($lectureDate, self::TIMEZONE);

        return Timetable::where('teacher_id', $teacher->id)
            ->where('batch_id', $batchId)
            ->where('status', 'scheduled')
            ->where(function ($query) use ($date) {
                $query->whereDate('schedule_date', $date->toDateString())
                    ->orWhere(function ($recurring) use ($date) {
                        $recurring->whereNull('schedule_date')
                            ->whereRaw('LOWER(day_of_week) = ?', [strtolower($date->format('l'))]);
                    });
            })
            ->orderByRaw('schedule_date is null')
            ->orderBy('start_time')
            ->first();
    }

    /**
     * Real punch in/out times for this teacher+batch+date, if the teacher has completed
     * attendance (punched both in and out) for that class. Returns null when attendance is
     * missing or still incomplete (punched in but not yet out) — callers should fall back to
     * the scheduled timetable time in that case, since there's nothing real to use yet.
     */
    private function attendanceActualTimes(int $teacherId, int $batchId, string $lectureDate): ?array
    {
        $attendance = StaffAttendance::where('teacher_id', $teacherId)
            ->where('batch_id', $batchId)
            ->where('attendance_date', $lectureDate)
            ->first();

        if (! $attendance || ! $attendance->first_in_time || ! $attendance->last_out_time) {
            return null;
        }

        return [
            'actual_start_time' => Carbon::parse($attendance->first_in_time)->format('H:i'),
            'actual_end_time' => Carbon::parse($attendance->last_out_time)->format('H:i'),
        ];
    }

    private function timetablePayload(Teacher $teacher, Timetable $timetable): array
    {
        $startTime = Carbon::parse($timetable->start_time)->format('H:i');
        $endTime = Carbon::parse($timetable->end_time)->format('H:i');

        return [
            'teacher_id' => $teacher->id,
            'branch_id' => $timetable->branch_id ?? $teacher->branch_id,
            'batch_id' => $timetable->batch_id,
            'subject_id' => $timetable->subject_id,
            'scheduled_start_time' => $startTime,
            'scheduled_end_time' => $endTime,
            'actual_start_time' => $startTime,
            'actual_end_time' => $endTime,
            'branch_name' => $timetable->branch->name ?? null,
            'subject_name' => $timetable->subject->name ?? null,
        ];
    }

    private function assertSubmissionWindow(string $lectureDate): void
    {
        $date = Carbon::parse($lectureDate, self::TIMEZONE)->startOfDay();
        $now = now(self::TIMEZONE);

        abort_if($date->greaterThan($now->copy()->startOfDay()), Response::HTTP_UNPROCESSABLE_ENTITY, 'Future date faculty log cannot be created.');
        abort_if($now->greaterThan($date->copy()->endOfDay()), Response::HTTP_UNPROCESSABLE_ENTITY, 'Faculty log can be submitted only before day end of the selected lecture date.');
    }

    private function assertTeacherCanEdit(FacultyLogBook $facultyLogBook, Teacher $teacher): void
    {
        abort_if($facultyLogBook->teacher_id !== $teacher->id, Response::HTTP_FORBIDDEN, '403 Forbidden');
        abort_if($facultyLogBook->approval_status === 'approved', Response::HTTP_UNPROCESSABLE_ENTITY, 'Approved faculty log cannot be edited.');

        $deadline = Carbon::parse($facultyLogBook->lecture_date, self::TIMEZONE)->endOfDay();
        abort_if(now(self::TIMEZONE)->greaterThan($deadline), Response::HTTP_UNPROCESSABLE_ENTITY, 'Faculty log editing time is over for this lecture date.');
    }

    /**
     * An existing log entry represents one specific lecture session. Editing it may only
     * correct the topic/homework notes — the batch and lecture date it was created for must
     * stay fixed, otherwise the row would silently start representing a different session
     * (mismatched batch/subject/time between the original submission and the edit).
     */
    private function assertSameLectureSession(Request $request, FacultyLogBook $facultyLogBook): void
    {
        $submittedBatchId = (int) $request->input('batch_id');
        $submittedDate = $request->input('lecture_date')
            ? Carbon::parse($request->input('lecture_date'), self::TIMEZONE)->toDateString()
            : null;
        $originalDate = Carbon::parse($facultyLogBook->lecture_date, self::TIMEZONE)->toDateString();

        abort_if(
            $submittedBatchId !== (int) $facultyLogBook->batch_id || $submittedDate !== $originalDate,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Batch and lecture date cannot be changed once a log is submitted, as that would log a different class session.'
        );
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'batch_id' => ['required', 'exists:batches,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'lecture_date' => ['required', 'date'],
            'scheduled_start_time' => ['required', 'date_format:H:i'],
            'scheduled_end_time' => ['required', 'date_format:H:i'],
            'actual_start_time' => ['required', 'date_format:H:i'],
            'actual_end_time' => ['required', 'date_format:H:i'],
            'topic_taught' => ['required', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'log_status' => ['required', 'in:submitted'],
            'approval_status' => ['required', 'in:pending'],
        ]);
    }

    private function prepare(array $data, SalaryCalculationService $salaryService): array
    {
        $minutes = $salaryService->payableMinutes($data['scheduled_start_time'], $data['scheduled_end_time'], $data['actual_start_time'], $data['actual_end_time']);
        $data['scheduled_minutes'] = $minutes['scheduled_minutes'];
        $data['salary_minutes'] = $minutes['salary_minutes'];
        $data['is_salary_eligible'] = false;
        $data['approved_by'] = null;
        $data['approved_at'] = null;

        return $data;
    }

    public function show(FacultyLogBook $faculty_log_book)
{
    $this->flagVerifiedActualTimes(collect([$faculty_log_book]));

    return view('admin.facultyLogBooks.show', compact('faculty_log_book'));
}
}
