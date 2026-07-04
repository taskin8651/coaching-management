<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\TimetableSubstitution;
use App\Services\WhatsappService;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TimetableSubstitutionsController extends Controller
{
    use AppliesErpScope;

    public function index()
    {
        abort_if(Gate::denies('timetable_substitute'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = TimetableSubstitution::with([
            'timetable.branch',
            'timetable.batch',
            'timetable.subject',
            'originalTeacher.user',
            'substituteTeacher.user',
            'changedBy',
        ]);

        if (! $this->erpScope()['is_admin']) {
            $query->whereHas('timetable', function ($q) {
                $this->scopeBranchQuery($q);
            });
        }

        $substitutions = $query->latest()->get();

        return view('admin.timetableSubstitutions.index', compact('substitutions'));
    }

    public function create()
    {
        abort_if(Gate::denies('timetable_substitute'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.timetableSubstitutions.create', $this->formData());
    }

    public function freeTeachers(Request $request)
    {
        abort_if(Gate::denies('timetable_substitute'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validate([
            'timetable_id' => ['required', 'exists:timetables,id'],
            'substitution_date' => ['required', 'date'],
            'ignore_substitution_id' => ['nullable', 'integer', 'exists:timetable_substitutions,id'],
        ]);

        $timetable = Timetable::findOrFail($data['timetable_id']);
        $this->assertBranchAccess($timetable);

        $teachers = $this->scopeBranchQuery(Teacher::with('user'))
            ->get()
            ->filter(function ($teacher) use ($timetable, $data) {
                return $this->isTeacherFree(
                    $teacher->id,
                    $timetable,
                    $data['substitution_date'],
                    $data['ignore_substitution_id'] ?? null
                );
            })
            ->map(fn ($teacher) => [
                'id' => $teacher->id,
                'name' => $teacher->user->name ?? 'Teacher #' . $teacher->id,
            ])
            ->values();

        return response()->json(['teachers' => $teachers]);
    }

    public function store(Request $request, WhatsappService $whatsapp)
    {
        abort_if(Gate::denies('timetable_substitute'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $this->validated($request);
        $timetable = Timetable::with(['batch.assignedStudents.user'])->findOrFail($data['timetable_id']);

        $this->assertBranchAccess($timetable);
        $this->assertValidSubstituteTeacher($data['substitute_teacher_id'], $timetable);
        $this->assertNotDuplicate($data['timetable_id'], $data['substitution_date']);

        $substitution = TimetableSubstitution::create($data + [
            'original_teacher_id' => $timetable->teacher_id,
            'changed_by_id'       => auth()->id(),
        ]);

        $timetable->update(['status' => 'changed']);

        $this->notifyStudents($timetable, $data['substitution_date'], $whatsapp);

        return redirect()
            ->route('admin.timetable-substitutions.index')
            ->with('message', 'Substitute teacher assigned successfully.');
    }

    public function show(TimetableSubstitution $timetableSubstitution)
    {
        abort_if(Gate::denies('timetable_substitute'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $timetableSubstitution->load([
            'timetable.branch',
            'timetable.course',
            'timetable.batch',
            'timetable.subject',
            'originalTeacher.user',
            'substituteTeacher.user',
            'changedBy',
        ]);

        $this->assertBranchAccess($timetableSubstitution->timetable);

        return view('admin.timetableSubstitutions.show', compact('timetableSubstitution'));
    }

    public function edit(TimetableSubstitution $timetableSubstitution)
    {
        abort_if(Gate::denies('timetable_substitute'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $timetableSubstitution->load('timetable');
        $this->assertBranchAccess($timetableSubstitution->timetable);

        return view('admin.timetableSubstitutions.edit', $this->formData($timetableSubstitution) + compact('timetableSubstitution'));
    }

    public function update(Request $request, TimetableSubstitution $timetableSubstitution)
    {
        abort_if(Gate::denies('timetable_substitute'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $timetableSubstitution->load('timetable');
        $this->assertBranchAccess($timetableSubstitution->timetable);

        $data = $this->validated($request);
        $timetable = Timetable::findOrFail($data['timetable_id']);

        $this->assertBranchAccess($timetable);
        $this->assertValidSubstituteTeacher($data['substitute_teacher_id'], $timetable);
        $this->assertNotDuplicate($data['timetable_id'], $data['substitution_date'], $timetableSubstitution->id);

        $timetableSubstitution->update($data + [
            'original_teacher_id' => $timetable->teacher_id,
            'changed_by_id'       => auth()->id(),
        ]);

        $timetable->update(['status' => 'changed']);

        return redirect()
            ->route('admin.timetable-substitutions.index')
            ->with('message', 'Substitute teacher assignment updated successfully.');
    }

    public function destroy(TimetableSubstitution $timetableSubstitution)
    {
        abort_if(Gate::denies('timetable_substitute'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $timetableSubstitution->load('timetable');
        $this->assertBranchAccess($timetableSubstitution->timetable);

        $timetable = $timetableSubstitution->timetable;
        $timetableSubstitution->delete();

        if ($timetable && ! $timetable->substitutions()->exists() && $timetable->status === 'changed') {
            $timetable->update(['status' => 'scheduled']);
        }

        return redirect()
            ->route('admin.timetable-substitutions.index')
            ->with('message', 'Substitute teacher assignment deleted successfully.');
    }

    private function formData(?TimetableSubstitution $substitution = null): array
    {
        $timetables = $this->scopeBranchQuery(
                Timetable::with(['batch', 'subject', 'teacher.user'])
            )
            ->orderBy('day_of_week')
            ->orderBy('schedule_date')
            ->orderBy('start_time')
            ->get()
            ->mapWithKeys(function ($timetable) {
                $date = $timetable->schedule_date
                    ? $timetable->schedule_date->format('d M Y')
                    : ($timetable->day_of_week ?: 'Weekly');

                $label = trim(sprintf(
                    '%s | %s | %s | %s-%s | %s',
                    $timetable->batch->name ?? 'Batch',
                    $timetable->subject->name ?? 'Subject',
                    $timetable->teacher->user->name ?? 'Teacher',
                    $timetable->start_time ?? '-',
                    $timetable->end_time ?? '-',
                    $date
                ));

                return [$timetable->id => $label];
            })
            ->prepend('Select Timetable', '');

        $teachers = $this->scopeBranchQuery(Teacher::with('user'))
            ->get()
            ->mapWithKeys(fn ($teacher) => [
                $teacher->id => $teacher->user->name ?? 'Teacher #' . $teacher->id,
            ])
            ->prepend('Select Substitute Teacher', '');

        $timetableDetails = $this->scopeBranchQuery(Timetable::query())
            ->get(['id', 'teacher_id', 'start_time', 'end_time', 'day_of_week', 'schedule_date'])
            ->mapWithKeys(fn ($timetable) => [
                $timetable->id => [
                    'teacher_id' => $timetable->teacher_id,
                    'start_time' => $timetable->start_time,
                    'end_time' => $timetable->end_time,
                    'day_of_week' => $timetable->day_of_week,
                    'schedule_date' => $timetable->schedule_date ? Carbon::parse($timetable->schedule_date)->format('Y-m-d') : null,
                ],
            ]);

        return compact('timetables', 'teachers', 'timetableDetails');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'timetable_id'           => ['required', 'exists:timetables,id'],
            'substitute_teacher_id'  => ['required', 'exists:teachers,id'],
            'substitution_date'      => ['required', 'date'],
            'reason'                 => ['nullable', 'string'],
            'change_note'            => ['nullable', 'string'],
        ]);
    }

    private function assertValidSubstituteTeacher(int $substituteTeacherId, Timetable $timetable): void
    {
        abort_if(
            $timetable->teacher_id && (int) $timetable->teacher_id === (int) $substituteTeacherId,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Substitute teacher cannot be the same as original teacher.'
        );

        $teacher = Teacher::findOrFail($substituteTeacherId);
        $this->assertBranchAccess($teacher);

        abort_if(
            ! $this->isTeacherFree($substituteTeacherId, $timetable, request('substitution_date'), request()->route('timetableSubstitution')?->id),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Selected substitute teacher is not free in this timetable slot.'
        );
    }

    private function assertNotDuplicate(int $timetableId, string $date, ?int $ignoreId = null): void
    {
        $exists = TimetableSubstitution::where('timetable_id', $timetableId)
            ->whereDate('substitution_date', $date)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();

        abort_if(
            $exists,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Substitute teacher is already assigned for this timetable and date.'
        );
    }

    private function notifyStudents(Timetable $timetable, string $date, WhatsappService $whatsapp): void
    {
        if (! $timetable->batch) {
            return;
        }

        foreach ($timetable->batch->assignedStudents()->with('user')->get() as $student) {
            $whatsapp->sendStudentGuardianMessage(
                $student,
                'timetable',
                'Timetable changed for ' . $timetable->batch->name . ' on ' . $date . '.'
            );
        }
    }

    private function isTeacherFree(int $teacherId, Timetable $targetTimetable, string $substitutionDate, ?int $ignoreSubstitutionId = null): bool
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
            ->when($ignoreSubstitutionId, fn ($query) => $query->where('id', '!=', $ignoreSubstitutionId))
            ->whereHas('timetable', function ($query) use ($start, $end) {
                $query->where('status', '!=', 'cancelled')
                    ->whereTime('start_time', '<', $end)
                    ->whereTime('end_time', '>', $start);
            })
            ->exists();
    }
}
