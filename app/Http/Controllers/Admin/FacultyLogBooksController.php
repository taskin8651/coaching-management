<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\FacultyLogBook;
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

        return view('admin.facultyLogBooks.index', ['logs' => $logs->latest()->get()]);
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
        $data = $this->prepare($this->validated($request), $salaryService);

        abort_if(FacultyLogBook::where('unique_key', FacultyLogBook::makeUniqueKey($data))->exists(), Response::HTTP_UNPROCESSABLE_ENTITY, 'Faculty log already exists for this teacher, batch and timetable slot.');
        FacultyLogBook::create($data);

        return redirect()->route('admin.faculty-log-books.index')->with('message', 'Faculty log saved successfully.');
    }

    public function edit(FacultyLogBook $facultyLogBook)
    {
        abort_if(Gate::denies('faculty_log_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $teacher = $this->loggedInTeacher();
        $this->assertTeacherCanEdit($facultyLogBook, $teacher);

        return view('admin.facultyLogBooks.edit', $this->formData($teacher) + compact('facultyLogBook'));
    }

    public function update(Request $request, FacultyLogBook $facultyLogBook, SalaryCalculationService $salaryService)
    {
        abort_if(Gate::denies('faculty_log_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $teacher = $this->loggedInTeacher();
        $this->assertTeacherCanEdit($facultyLogBook, $teacher);

        $this->mergeTrustedTimetableData($request, $teacher);
        $data = $this->prepare($this->validated($request), $salaryService);
        abort_if(FacultyLogBook::where('unique_key', FacultyLogBook::makeUniqueKey($data))->where('id', '!=', $facultyLogBook->id)->exists(), Response::HTTP_UNPROCESSABLE_ENTITY, 'Faculty log already exists for this teacher, batch and timetable slot.');

        $facultyLogBook->update($data);

        return redirect()->route('admin.faculty-log-books.index')->with('message', 'Faculty log updated successfully.');
    }

    public function approve(FacultyLogBook $facultyLogBook)
    {
        abort_if(Gate::denies('faculty_log_approve'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->assertBranchAccess($facultyLogBook);
        $facultyLogBook->update([
            'approval_status' => 'approved',
            'is_salary_eligible' => $facultyLogBook->salary_minutes > 0,
            'approved_by' => auth()->id(),
            'approved_at' => now(self::TIMEZONE),
        ]);

        return back()->with('message', 'Faculty log approved successfully.');
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
}
