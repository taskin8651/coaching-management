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

    return view('admin.timetables.index', compact(
        'timetables',
        'days',
        'timeSlots',
        'teacherWiseTimetables',
        'isStudent'
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
}