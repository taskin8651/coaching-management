<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\TimetableSubstitution;
use App\Services\WhatsappService;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TimetablesController extends Controller
{
    use AppliesErpScope;

 public function index()
{
    abort_if(Gate::denies('timetable_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

    $scope = $this->erpScope();

    $isStudent = $scope['is_student'] ?? false;

    $timetables = Timetable::with([
        'branch',
        'course',
        'batch',
        'subject',
        'teacher.user'
    ]);

    if ($isStudent) {
        $timetables->whereHas('batch.assignedStudents', function ($query) {
            $query->where('user_id', auth()->id());
        });
    } elseif ($scope['is_teacher'] && $scope['teacher_id']) {
        $timetables->where('teacher_id', $scope['teacher_id']);
    } elseif (! $scope['is_admin']) {
        $this->scopeBranchQuery($timetables);
    }

    $timetables = $timetables->latest()->get();

    $days = [
        'Monday'    => 'Mon',
        'Tuesday'   => 'Tue',
        'Wednesday' => 'Wed',
        'Thursday'  => 'Thu',
        'Friday'    => 'Fri',
        'Saturday'  => 'Sat',
    ];

    $timeSlots = $timetables
        ->map(function ($item) {
            return $item->start_time . '-' . $item->end_time;
        })
        ->unique()
        ->sort()
        ->values();

    $teacherWiseTimetables = $timetables->groupBy('teacher_id');

    $teachers = $this->scopeBranchQuery(Teacher::with('user'))
        ->get()
        ->mapWithKeys(fn($t) => [
            $t->id => $t->user->name ?? 'Teacher #' . $t->id
        ])
        ->prepend('Select Teacher', '');

    return view('admin.timetables.index', compact(
        'timetables',
        'days',
        'timeSlots',
        'teacherWiseTimetables',
        'isStudent',
        'teachers'
    ));
}

    public function create()
    {
        abort_if(Gate::denies('timetable_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.timetables.create', $this->formData());
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('timetable_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $this->validated($request);
        $scope = $this->erpScope();

        if (! $scope['is_admin']) {
            $data['branch_id'] = $scope['branch_id'];
        }

        Timetable::create($data);

        return redirect()
            ->route('admin.timetables.index')
            ->with('message', 'Timetable saved successfully.');
    }

    public function edit(Timetable $timetable)
    {
        abort_if(Gate::denies('timetable_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->assertBranchAccess($timetable);

        return view('admin.timetables.edit', $this->formData() + compact('timetable'));
    }

    public function update(Request $request, Timetable $timetable)
    {
        abort_if(Gate::denies('timetable_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->assertBranchAccess($timetable);

        $data = $this->validated($request);
        $scope = $this->erpScope();

        if (! $scope['is_admin']) {
            $data['branch_id'] = $scope['branch_id'];
        }

        $timetable->update($data);

        return redirect()
            ->route('admin.timetables.index')
            ->with('message', 'Timetable updated successfully.');
    }

    public function destroy(Timetable $timetable)
    {
        abort_if(Gate::denies('timetable_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->assertBranchAccess($timetable);

        $timetable->delete();

        return back()->with('message', 'Timetable deleted successfully.');
    }

    public function substitute(Request $request, Timetable $timetable, WhatsappService $whatsapp)
    {
        abort_if(Gate::denies('timetable_substitute'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->assertBranchAccess($timetable);

        $data = $request->validate([
            'substitute_teacher_id' => ['required', 'exists:teachers,id'],
            'substitution_date'    => ['required', 'date'],
            'reason'               => ['nullable', 'string'],
            'change_note'          => ['nullable', 'string'],
        ]);

        abort_if(
            $timetable->teacher_id && (int) $timetable->teacher_id === (int) $data['substitute_teacher_id'],
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Substitute teacher cannot be the same as original teacher.'
        );

        abort_if(
            TimetableSubstitution::where('timetable_id', $timetable->id)
                ->whereDate('substitution_date', $data['substitution_date'])
                ->exists(),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Substitute teacher is already assigned for this timetable and date.'
        );

        $substituteTeacher = Teacher::findOrFail($data['substitute_teacher_id']);
        $this->assertBranchAccess($substituteTeacher);

        abort_if(
            ! $this->isTeacherFree($substituteTeacher->id, $timetable, $data['substitution_date']),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Selected substitute teacher is not free in this timetable slot.'
        );

        $substitution = TimetableSubstitution::create($data + [
            'timetable_id'        => $timetable->id,
            'original_teacher_id' => $timetable->teacher_id,
            'changed_by_id'       => auth()->id(),
        ]);

        $timetable->update([
            'status' => 'changed',
        ]);

        foreach ($timetable->batch->assignedStudents()->with('user')->get() as $student) {
            $whatsapp->sendStudentGuardianMessage(
                $student,
                'timetable',
                'Timetable changed for ' . $timetable->batch->name . ' on ' . $data['substitution_date'] . '.'
            );
        }

        return back()->with('message', 'Substitute teacher assigned successfully.');
    }

    private function formData(): array
    {
        return [
            'branches' => $this->scopeBranchQuery(Branch::query(), 'id')
                ->pluck('name', 'id')
                ->prepend('Optional', ''),

            'courses' => $this->scopeBranchQuery(Course::query())
                ->pluck('name', 'id')
                ->prepend('Optional', ''),

            'batches' => $this->scopeBatchQuery(Batch::query())
                ->pluck('name', 'id')
                ->prepend(trans('global.pleaseSelect'), ''),

            'subjects' => $this->scopeBranchQuery(Subject::query())
                ->pluck('name', 'id')
                ->prepend('Optional', ''),

            'teachers' => $this->scopeBranchQuery(Teacher::with('user'))
                ->get()
                ->mapWithKeys(fn($t) => [
                    $t->id => $t->user->name ?? 'Teacher #' . $t->id
                ])
                ->prepend('Optional', ''),

            'batchesByBranch' => $this->batchesByBranch(),
            'coursesByBatch' => $this->coursesByBatch(),
            'subjectsByBatch' => $this->subjectsByBatch(),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'branch_id'     => ['nullable', 'exists:branches,id'],
            'course_id'     => ['nullable', 'exists:courses,id'],
            'batch_id'      => ['required', 'exists:batches,id'],
            'subject_id'    => ['nullable', 'exists:subjects,id'],
            'teacher_id'    => ['nullable', 'exists:teachers,id'],
            'day_of_week'   => ['nullable', 'string', 'max:20'],
            'schedule_date' => ['nullable', 'date'],
            'start_time'    => ['required', 'date_format:H:i'],
            'end_time'      => ['required', 'date_format:H:i'],
            'room'          => ['nullable', 'string', 'max:255'],
            'status'        => ['required', 'in:scheduled,changed,cancelled'],
        ]);
    }

    private function isTeacherFree(int $teacherId, Timetable $targetTimetable, string $substitutionDate): bool
    {
        if ($targetTimetable->teacher_id && (int) $targetTimetable->teacher_id === (int) $teacherId) {
            return false;
        }

        $date = Carbon::parse($substitutionDate);
        $day = strtolower($date->format('l'));
        $start = Carbon::parse($targetTimetable->start_time)->format('H:i:s');
        $end = Carbon::parse($targetTimetable->end_time)->format('H:i:s');

        $hasTimetableClash = Timetable::where('teacher_id', $teacherId)
            ->where('id', '!=', $targetTimetable->id)
            ->where('status', '!=', 'cancelled')
            ->whereTime('start_time', '<', $end)
            ->whereTime('end_time', '>', $start)
            ->where(function ($query) use ($date, $day) {
                $query->whereDate('schedule_date', $date->toDateString())
                    ->orWhere(function ($q) use ($day) {
                        $q->whereNull('schedule_date')
                            ->whereRaw('LOWER(day_of_week) = ?', [$day]);
                    });
            })
            ->exists();

        if ($hasTimetableClash) {
            return false;
        }

        return ! TimetableSubstitution::where('substitute_teacher_id', $teacherId)
            ->whereDate('substitution_date', $date->toDateString())
            ->whereHas('timetable', function ($query) use ($start, $end) {
                $query->where('status', '!=', 'cancelled')
                    ->whereTime('start_time', '<', $end)
                    ->whereTime('end_time', '>', $start);
            })
            ->exists();
    }
}
