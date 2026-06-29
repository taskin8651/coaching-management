<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\TimetableSubstitution;
use App\Services\WhatsappService;
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

        return compact('timetables', 'teachers');
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
}
