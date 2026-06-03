<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Models\Batch;
use App\Models\ExtraClass;
use App\Models\Subject;
use App\Models\Teacher;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExtraClassesController extends Controller
{
    use AppliesErpScope;

    public function index()
    {
        abort_if(Gate::denies('extra_class_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $extraClasses = ExtraClass::with(['teacher.user', 'batch', 'subject', 'assignedBy', 'approvedBy']);
        $scope = $this->erpScope();
        if ($scope['is_teacher'] && $scope['teacher_id']) {
            $extraClasses->where('teacher_id', $scope['teacher_id']);
        } elseif (! $scope['is_admin']) {
            $this->scopeBranchQuery($extraClasses);
        }
        $extraClasses = $extraClasses->latest()->get();

        return view('admin.extraClasses.index', compact('extraClasses'));
    }

    public function create()
    {
        abort_if(Gate::denies('extra_class_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.extraClasses.create', $this->formData());
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('extra_class_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        ExtraClass::create($this->prepare($this->validated($request)));

        return redirect()->route('admin.extra-classes.index')->with('message', 'Extra class saved successfully.');
    }

    public function edit(ExtraClass $extraClass)
    {
        abort_if(Gate::denies('extra_class_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->assertBranchAccess($extraClass);
        return view('admin.extraClasses.edit', $this->formData() + compact('extraClass'));
    }

    public function update(Request $request, ExtraClass $extraClass)
    {
        abort_if(Gate::denies('extra_class_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->assertBranchAccess($extraClass);
        $extraClass->update($this->prepare($this->validated($request), $extraClass));

        return redirect()->route('admin.extra-classes.index')->with('message', 'Extra class updated successfully.');
    }

    public function approve(ExtraClass $extraClass)
    {
        abort_if(Gate::denies('extra_class_approve'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->assertBranchAccess($extraClass);

        $extraClass->update([
            'approval_status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        return back()->with('message', 'Extra class approved successfully.');
    }

    public function reject(ExtraClass $extraClass)
    {
        abort_if(Gate::denies('extra_class_approve'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $this->assertBranchAccess($extraClass);

        $extraClass->update([
            'approval_status' => 'rejected',
            'approved_by' => auth()->id(),
            'salary_minutes' => 0,
            'salary_amount' => 0,
        ]);

        return back()->with('message', 'Extra class rejected successfully.');
    }

    private function formData(): array
    {
        return [
            'teachers' => $this->scopeBranchQuery(Teacher::with('user'))->get()->mapWithKeys(fn ($teacher) => [$teacher->id => $teacher->user->name ?? ('Teacher #' . $teacher->id)])->prepend(trans('global.pleaseSelect'), ''),
            'batches' => $this->scopeBatchQuery(Batch::query())->pluck('name', 'id')->prepend('Optional', ''),
            'subjects' => $this->scopeBranchQuery(Subject::query())->pluck('name', 'id')->prepend('Optional', ''),
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'batch_id' => ['nullable', 'exists:batches,id'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'class_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'reason' => ['nullable', 'string'],
            'approval_status' => ['required', 'in:pending,approved,rejected'],
            'salary_amount' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ]);
    }

    private function prepare(array $data, ExtraClass $extraClass = null): array
    {
        $teacher = Teacher::find($data['teacher_id']);
        $data['branch_id'] = $teacher->branch_id ?? null;
        $data['assigned_by'] = $extraClass->assigned_by ?? auth()->id();
        $data['salary_minutes'] = Carbon::parse($data['start_time'])->diffInMinutes(Carbon::parse($data['end_time']));
        $data['salary_amount'] = (float) ($data['salary_amount'] ?? ($data['salary_minutes'] * (float) ($teacher->minute_rate ?? 0)));

        if ($data['approval_status'] === 'approved') {
            $data['approved_by'] = auth()->id();
        }

        if ($data['approval_status'] !== 'approved') {
            $data['salary_amount'] = 0;
        }

        return $data;
    }
}
