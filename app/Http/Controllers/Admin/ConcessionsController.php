<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConcessionRequest;
use App\Http\Requests\UpdateConcessionRequest;
use App\Models\Concession;
use App\Models\Student;
use App\Models\StudentFeeLedger;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConcessionsController extends Controller
{
    use AppliesErpScope;

    public function index()
    {
        abort_if(Gate::denies('concession_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $concessions = Concession::with(['student.user', 'ledger.feeStructure', 'approvedBy'])
            ->whereHas('student', fn ($q) => $this->scopeStudentQuery($q))
            ->latest()
            ->get();

        return view('admin.concessions.index', compact('concessions'));
    }

    public function create()
    {
        abort_if(Gate::denies('concession_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.concessions.create', $this->formData());
    }

    public function store(StoreConcessionRequest $request)
    {
        $data = $request->validated();

        $student = $this->scopeStudentQuery(Student::query())->find($data['student_id']);
        abort_if(! $student, Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ledger = StudentFeeLedger::where('student_id', $student->id)->where('status', 'active')->latest('id')->first();

        abort_if(
            ! $ledger,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'This student has no active fee ledger yet. Assign a fee structure to the student before adding a concession.'
        );

        $data['student_fee_ledger_id'] = $ledger->id;
        $data['approval_status'] = 'pending';
        $data['status'] = 'active';
        $data['created_by_id'] = auth()->id();

        Concession::create($data);

        return redirect()->route('admin.concessions.index')->with('message', 'Concession request submitted successfully.');
    }

    public function show(Concession $concession)
    {
        abort_if(Gate::denies('concession_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($concession);

        $concession->load(['student.user', 'ledger.feeStructure', 'approvedBy', 'createdBy']);

        return view('admin.concessions.show', compact('concession'));
    }

    public function edit(Concession $concession)
    {
        abort_if(Gate::denies('concession_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($concession);

        abort_if(
            $concession->approval_status === 'approved',
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Approved concessions cannot be edited. Reject it and create a new one if a change is needed.'
        );

        return view('admin.concessions.edit', $this->formData() + compact('concession'));
    }

    public function update(UpdateConcessionRequest $request, Concession $concession)
    {
        $this->checkAccess($concession);

        abort_if(
            $concession->approval_status === 'approved',
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Approved concessions cannot be edited. Reject it and create a new one if a change is needed.'
        );

        $concession->update($request->validated());

        return redirect()->route('admin.concessions.index')->with('message', 'Concession updated successfully.');
    }

    public function destroy(Concession $concession)
    {
        abort_if(Gate::denies('concession_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($concession);

        abort_if(
            $concession->approval_status !== 'pending',
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Only a pending concession can be deleted.'
        );

        $concession->delete();

        return back()->with('message', 'Concession deleted successfully.');
    }

    public function approve(Concession $concession)
    {
        abort_if(Gate::denies('concession_approve'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($concession);

        $concession->update([
            'approval_status' => 'approved',
            'approved_by_id' => auth()->id(),
            'approval_date' => now()->format('Y-m-d'),
        ]);

        $concession->ledger?->recalculate();

        return back()->with('message', 'Concession approved successfully.');
    }

    public function reject(Concession $concession)
    {
        abort_if(Gate::denies('concession_approve'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($concession);

        $concession->update([
            'approval_status' => 'rejected',
            'approved_by_id' => auth()->id(),
            'approval_date' => now()->format('Y-m-d'),
        ]);

        $concession->ledger?->recalculate();

        return back()->with('message', 'Concession rejected.');
    }

    private function formData(): array
    {
        return [
            'students' => $this->scopeStudentQuery(Student::with('user'))
                ->get()
                ->mapWithKeys(fn ($s) => [$s->id => $s->user->name ?? $s->student_code ?? 'Student #' . $s->id])
                ->prepend(trans('global.pleaseSelect'), ''),
        ];
    }

    private function checkAccess(Concession $concession): void
    {
        $scope = $this->erpScope();

        if ($scope['is_admin']) {
            return;
        }

        abort_if(
            ! $this->scopeStudentQuery(Student::query())->where('id', $concession->student_id)->exists(),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden'
        );
    }
}
