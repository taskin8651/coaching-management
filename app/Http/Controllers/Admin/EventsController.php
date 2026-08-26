<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Event;
use App\Models\EventEnrollment;
use App\Models\EventFeeRule;
use App\Models\Student;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EventsController extends Controller
{
    use AppliesErpScope;

    public function index()
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $events = Event::with(['branch'])->withCount('enrollments');
        $scope = $this->erpScope();

        if (! $scope['is_admin']) {
            $scope['branch_id']
                ? $events->where(fn ($q) => $q->where('branch_id', $scope['branch_id'])->orWhereNull('branch_id'))
                : $events->whereRaw('1 = 0');
        }

        $events = $events->latest()->get();

        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        abort_if(Gate::denies('event_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.events.create', $this->formData());
    }

    public function store(StoreEventRequest $request)
    {
        $data = $request->validated();

        if (! auth()->user()->is_admin) {
            $branchId = $this->erpScope()['branch_id'];

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;
        }

        $batchIds = $data['batch_ids'] ?? [];
        unset($data['batch_ids']);

        $data['external_enrollment_allowed'] = (bool) ($data['external_enrollment_allowed'] ?? false);
        $data['status'] = 'draft';
        $data['created_by_id'] = auth()->id();

        $event = DB::transaction(function () use ($data, $batchIds) {
            $event = Event::create($data);
            $event->batches()->sync($batchIds);

            return $event;
        });

        return redirect()->route('admin.events.show', $event)->with('message', 'Event created successfully.');
    }

    public function show(Event $event)
    {
        abort_if(Gate::denies('event_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($event);

        $event->load(['branch', 'batches', 'feeRules', 'createdBy']);
        $event->load(['enrollments' => fn ($q) => $q->with(['student.user', 'externalContact'])->latest()]);

        $stats = [
            'total' => $event->enrollments->where('status', '!=', 'cancelled')->count(),
            'students' => $event->enrollments->where('status', '!=', 'cancelled')->where('participant_type', 'student')->count(),
            'external' => $event->enrollments->where('status', '!=', 'cancelled')->where('participant_type', 'external')->count(),
            'paid' => $event->enrollments->where('payment_status', 'paid')->count(),
            'partial' => $event->enrollments->where('payment_status', 'partial')->count(),
            'unpaid' => $event->enrollments->where('payment_status', 'unpaid')->count(),
            'complimentary' => $event->enrollments->where('payment_status', 'complimentary')->count(),
            'revenue' => $event->enrollments->sum('paid_amount'),
            'attendance_marked' => $event->enrollments->whereNotNull('is_present')->count(),
            'present' => $event->enrollments->where('is_present', true)->count(),
            'certificates_issued' => $event->enrollments->where('certificate_status', 'issued')->count(),
        ];

        $eligibleBatchIds = $event->batches->pluck('id');

        $unenrolledStudents = Student::with('user')
            ->when($eligibleBatchIds->isNotEmpty(), fn ($q) => $q->whereIn('batch_id', $eligibleBatchIds))
            ->when($eligibleBatchIds->isEmpty(), fn ($q) => $q->whereRaw('1 = 0'))
            ->whereDoesntHave('eventEnrollments', fn ($q) => $q->where('event_id', $event->id)->where('status', '!=', 'cancelled'))
            ->get();

        $feeAccounts = \App\Models\FeeAccount::where('status', 'active')->orderBy('name')->pluck('name', 'id');

        return view('admin.events.show', compact('event', 'stats', 'unenrolledStudents', 'feeAccounts'));
    }

    public function edit(Event $event)
    {
        abort_if(Gate::denies('event_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($event);

        $event->load(['batches']);

        return view('admin.events.edit', $this->formData() + compact('event'));
    }

    public function update(UpdateEventRequest $request, Event $event)
    {
        $this->checkAccess($event);

        $data = $request->validated();

        if (! auth()->user()->is_admin) {
            $branchId = $this->erpScope()['branch_id'];

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;
        }

        $batchIds = $data['batch_ids'] ?? [];
        unset($data['batch_ids'], $data['status']);

        $data['external_enrollment_allowed'] = (bool) ($data['external_enrollment_allowed'] ?? false);

        DB::transaction(function () use ($event, $data, $batchIds) {
            $event->update($data);
            $event->batches()->sync($batchIds);
        });

        return redirect()->route('admin.events.show', $event)->with('message', 'Event updated successfully.');
    }

    public function destroy(Event $event)
    {
        abort_if(Gate::denies('event_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($event);

        abort_if(
            $event->enrollments()->exists(),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'This event has enrollments and cannot be deleted. Cancel the event instead.'
        );

        $event->delete();

        return back()->with('message', 'Event deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('event_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Event::whereIn('id', request('ids'))->whereDoesntHave('enrollments')->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function publish(Event $event)
    {
        return $this->transition($event, 'open');
    }

    public function close(Event $event)
    {
        return $this->transition($event, 'closed');
    }

    public function reopen(Event $event)
    {
        return $this->transition($event, 'open');
    }

    public function cancel(Event $event)
    {
        return $this->transition($event, 'cancelled');
    }

    private function transition(Event $event, string $to)
    {
        abort_if(Gate::denies('event_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($event);

        abort_if(
            ! in_array($to, $event->allowedTransitions()),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            "Cannot move this event from '{$event->status}' to '{$to}'."
        );

        $event->update(['status' => $to]);

        return back()->with('message', 'Event status updated to ' . ucfirst($to) . '.');
    }

    /**
     * Bulk-enrolls students from the event's eligible batches — mirrors
     * FeeStructuresController::assignToStudents() exactly: skip students already actively
     * enrolled, resolve each student's fee via EventFeeRule::resolveFor() with group_size = the
     * number of students selected in this single action (so a 5+ bulk enroll can trigger a
     * "group" rate), wrap in a transaction.
     */
    public function bulkEnroll(Event $event, Request $request)
    {
        abort_if(Gate::denies('event_enroll'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($event);

        abort_if(! $event->canEnroll(), Response::HTTP_UNPROCESSABLE_ENTITY, 'This event is not open for enrollment.');

        $studentIds = collect($request->input('student_ids', []))->filter()->unique()->values();

        abort_if($studentIds->isEmpty(), Response::HTTP_UNPROCESSABLE_ENTITY, 'Select at least one student.');

        $eligibleBatchIds = $event->batches->pluck('id');

        $students = $this->scopeStudentQuery(Student::query())
            ->whereIn('id', $studentIds)
            ->when($eligibleBatchIds->isNotEmpty(), fn ($q) => $q->whereIn('batch_id', $eligibleBatchIds))
            ->get();

        $groupSize = $students->count();
        $enrolled = 0;
        $skipped = 0;

        foreach ($students as $student) {
            if ($event->capacity && $event->enrollments()->where('status', '!=', 'cancelled')->count() >= $event->capacity) {
                break;
            }

            $alreadyEnrolled = EventEnrollment::where('event_id', $event->id)
                ->where('student_id', $student->id)
                ->where('status', '!=', 'cancelled')
                ->exists();

            if ($alreadyEnrolled) {
                $skipped++;

                continue;
            }

            DB::transaction(function () use ($event, $student, $groupSize) {
                $resolved = EventFeeRule::resolveFor($event, 'student', $groupSize, now());

                EventEnrollment::create([
                    'event_id' => $event->id,
                    'branch_id' => $student->branch_id,
                    'student_id' => $student->id,
                    'participant_type' => 'student',
                    'group_size' => $groupSize,
                    'fee_rule_label' => $resolved['label'],
                    'fee_amount' => $resolved['amount'],
                    'due_amount' => $resolved['amount'],
                    'payment_status' => 'unpaid',
                    'enrollment_date' => now()->format('Y-m-d'),
                    'status' => 'registered',
                    'enrolled_by_id' => auth()->id(),
                ]);
            });

            $enrolled++;
        }

        return back()->with('message', "Enrolled {$enrolled} student(s)." . ($skipped ? " Skipped {$skipped} already enrolled." : ''));
    }

    public function storeFeeRule(Event $event, Request $request)
    {
        abort_if(Gate::denies('event_fee_rule_manage'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($event);

        $data = $request->validate([
            'rule_type' => ['required', 'in:karmayoga_student,external_student,group,early_bird'],
            'label' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'min_group_size' => ['nullable', 'integer', 'min:1'],
            'valid_until' => ['nullable', 'date'],
        ]);

        $data['status'] = 'active';

        $event->feeRules()->create($data);

        return back()->with('message', 'Fee rule added successfully.');
    }

    public function updateFeeRule(Event $event, EventFeeRule $feeRule, Request $request)
    {
        abort_if(Gate::denies('event_fee_rule_manage'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($event);

        abort_if($feeRule->event_id !== $event->id, Response::HTTP_NOT_FOUND);

        $data = $request->validate([
            'rule_type' => ['required', 'in:karmayoga_student,external_student,group,early_bird'],
            'label' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'min_group_size' => ['nullable', 'integer', 'min:1'],
            'valid_until' => ['nullable', 'date'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $feeRule->update($data);

        return back()->with('message', 'Fee rule updated successfully.');
    }

    public function destroyFeeRule(Event $event, EventFeeRule $feeRule)
    {
        abort_if(Gate::denies('event_fee_rule_manage'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($event);

        abort_if($feeRule->event_id !== $event->id, Response::HTTP_NOT_FOUND);

        $feeRule->delete();

        return back()->with('message', 'Fee rule deleted successfully.');
    }

    private function formData(): array
    {
        $scope = $this->erpScope();

        $branches = Branch::where('status', 'active')
            ->when(! $scope['is_admin'], fn ($q) => $scope['branch_id'] ? $q->where('id', $scope['branch_id']) : $q->whereRaw('1 = 0'))
            ->pluck('name', 'id')
            ->prepend('Multi-Branch / HQ', '');

        $batches = Batch::where('status', 'active')
            ->when(! $scope['is_admin'], fn ($q) => $scope['branch_id'] ? $q->where('branch_id', $scope['branch_id']) : $q->whereRaw('1 = 0'))
            ->pluck('name', 'id');

        return [
            'branches' => $branches,
            'batches' => $batches,
            'batchesByBranch' => $this->batchesByBranch(),
        ];
    }

    private function checkAccess(Event $event): void
    {
        $scope = $this->erpScope();

        if ($scope['is_admin']) {
            return;
        }

        abort_if(
            $event->branch_id && $event->branch_id != $scope['branch_id'],
            Response::HTTP_FORBIDDEN,
            '403 Forbidden'
        );
    }
}
