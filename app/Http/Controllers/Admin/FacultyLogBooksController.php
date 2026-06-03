<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Models\Batch;
use App\Models\FacultyLogBook;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\SalaryCalculationService;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FacultyLogBooksController extends Controller
{
    use AppliesErpScope;

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

        return view('admin.facultyLogBooks.index', compact('logs'));
    }

    public function create()
    {
        abort_if(Gate::denies('faculty_log_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.facultyLogBooks.create', $this->formData());
    }

    public function store(Request $request, SalaryCalculationService $salaryService)
    {
        abort_if(Gate::denies('faculty_log_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $this->prepare($this->validated($request), $salaryService);
        FacultyLogBook::create($data);

        return redirect()->route('admin.faculty-log-books.index')->with('message', 'Faculty log saved successfully.');
    }

    public function edit(FacultyLogBook $facultyLogBook)
    {
        abort_if(Gate::denies('faculty_log_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->assertBranchAccess($facultyLogBook);
        return view('admin.facultyLogBooks.edit', $this->formData() + compact('facultyLogBook'));
    }

    public function update(Request $request, FacultyLogBook $facultyLogBook, SalaryCalculationService $salaryService)
    {
        abort_if(Gate::denies('faculty_log_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->assertBranchAccess($facultyLogBook);
        $facultyLogBook->update($this->prepare($this->validated($request), $salaryService));

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
            'approved_at' => now(),
        ]);

        return back()->with('message', 'Faculty log approved successfully.');
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
            'lecture_date' => ['required', 'date'],
            'scheduled_start_time' => ['required', 'date_format:H:i'],
            'scheduled_end_time' => ['required', 'date_format:H:i'],
            'actual_start_time' => ['nullable', 'date_format:H:i'],
            'actual_end_time' => ['nullable', 'date_format:H:i'],
            'topic_taught' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'log_status' => ['required', 'in:draft,submitted,missed,late_entry'],
            'approval_status' => ['required', 'in:pending,approved,rejected'],
        ]);
    }

    private function prepare(array $data, SalaryCalculationService $salaryService): array
    {
        $teacher = Teacher::find($data['teacher_id']);
        $minutes = $salaryService->payableMinutes(
            $data['scheduled_start_time'] ?? null,
            $data['scheduled_end_time'] ?? null,
            $data['actual_start_time'] ?? null,
            $data['actual_end_time'] ?? null
        );

        $data['branch_id'] = $teacher->branch_id ?? null;
        $data['scheduled_minutes'] = $minutes['scheduled_minutes'];
        $data['salary_minutes'] = $minutes['salary_minutes'];
        $data['is_salary_eligible'] = $data['approval_status'] === 'approved'
            && $data['log_status'] === 'submitted'
            && $data['salary_minutes'] > 0;

        if ($data['approval_status'] === 'approved') {
            $data['approved_by'] = auth()->id();
            $data['approved_at'] = now();
        }

        return $data;
    }
}
