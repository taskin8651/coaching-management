<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeeStructureRequest;
use App\Http\Requests\UpdateFeeStructureRequest;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\FeeStructure;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Teacher;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FeeStructuresController extends Controller
{
    use AppliesErpScope;
    public function index()
    {
        abort_if(Gate::denies('fee_structure_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $feeStructures = FeeStructure::with(['branch', 'course', 'batch']);

        if (auth()->user()->is_admin) {
            // Admin all
        } else {
            $branchId = $this->getUserBranchId();

            $branchId
                ? $feeStructures->where('branch_id', $branchId)
                : $feeStructures->whereRaw('1 = 0');
        }

        $feeStructures = $feeStructures->latest()->get();

        return view('admin.feeStructures.index', compact('feeStructures'));
    }

    public function create()
    {
        abort_if(Gate::denies('fee_structure_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branchId = $this->getUserBranchId();

        $branches = Branch::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $courses = Course::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $batches = Batch::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->pluck('name', 'id')
            ->prepend('All Batches / Optional', '');

        $coursesByBranch = $this->coursesByBranch();
        $batchesByBranchCourse = $this->batchesByBranchCourse();

        return view('admin.feeStructures.create', compact('branches', 'courses', 'batches', 'coursesByBranch', 'batchesByBranchCourse'));
    }

    public function store(StoreFeeStructureRequest $request)
    {
        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $this->prepareData($request->validated());

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;

            $this->validateBranchData($data, $branchId);
        }

        FeeStructure::create($data);

        return redirect()->route('admin.fee-structures.index')->with('message', 'Fee structure created successfully.');
    }

    public function show(FeeStructure $feeStructure)
    {
        abort_if(Gate::denies('fee_structure_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($feeStructure);

        $feeStructure->load(['branch', 'course', 'batch']);

        return view('admin.feeStructures.show', compact('feeStructure'));
    }

    public function edit(FeeStructure $feeStructure)
    {
        abort_if(Gate::denies('fee_structure_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($feeStructure);

        $branchId = $this->getUserBranchId();

        $branches = Branch::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $courses = Course::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $batches = Batch::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->pluck('name', 'id')
            ->prepend('All Batches / Optional', '');

        $coursesByBranch = $this->coursesByBranch();
        $batchesByBranchCourse = $this->batchesByBranchCourse();

        $feeStructure->load(['branch', 'course', 'batch']);

        return view('admin.feeStructures.edit', compact(
            'feeStructure',
            'branches',
            'courses',
            'batches',
            'coursesByBranch',
            'batchesByBranchCourse'
        ));
    }

    public function update(UpdateFeeStructureRequest $request, FeeStructure $feeStructure)
    {
        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($feeStructure);

        $data = $this->prepareData($request->validated());

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;

            $this->validateBranchData($data, $branchId);
        }

        $feeStructure->update($data);

        return redirect()->route('admin.fee-structures.index')->with('message', 'Fee structure updated successfully.');
    }

    public function destroy(FeeStructure $feeStructure)
    {
        abort_if(Gate::denies('fee_structure_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($feeStructure);

        $feeStructure->delete();

        return back()->with('message', 'Fee structure deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('fee_structure_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = FeeStructure::whereIn('id', request('ids'));

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
        }

        $query->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function prepareData(array $data): array
    {
        $admissionFee = (float) ($data['admission_fee'] ?? 0);
        $tuitionFee   = (float) ($data['tuition_fee'] ?? 0);
        $examFee      = (float) ($data['exam_fee'] ?? 0);
        $materialFee  = (float) ($data['material_fee'] ?? 0);
        $otherFee     = (float) ($data['other_fee'] ?? 0);

        $data['admission_fee'] = $admissionFee;
        $data['tuition_fee']   = $tuitionFee;
        $data['exam_fee']      = $examFee;
        $data['material_fee']  = $materialFee;
        $data['other_fee']     = $otherFee;

        $data['total_fee'] = $admissionFee + $tuitionFee + $examFee + $materialFee + $otherFee;

        return $data;
    }

    private function checkAccess(FeeStructure $feeStructure): void
    {
        if (auth()->user()->is_admin) {
            return;
        }

        $branchId = $this->getUserBranchId();

        abort_if(! $branchId || $feeStructure->branch_id != $branchId, Response::HTTP_FORBIDDEN, '403 Forbidden');
    }

    private function validateBranchData(array $data, $branchId): void
    {
        if (! empty($data['course_id'])) {
            abort_if(
                ! Course::where('id', $data['course_id'])->where('branch_id', $branchId)->exists(),
                Response::HTTP_FORBIDDEN,
                'Invalid course for your branch.'
            );
        }

        if (! empty($data['batch_id'])) {
            abort_if(
                ! Batch::where('id', $data['batch_id'])->where('branch_id', $branchId)->exists(),
                Response::HTTP_FORBIDDEN,
                'Invalid batch for your branch.'
            );
        }
    }

    private function getUserBranchId()
    {
        $user = auth()->user();

        if ($user->is_admin) {
            return null;
        }

        $managedBranch = Branch::where('manager_id', $user->id)->first();

        if ($managedBranch) {
            return $managedBranch->id;
        }

        $staff = Staff::where('user_id', $user->id)->first();

        if ($staff) {
            return $staff->branch_id;
        }

        $teacher = Teacher::where('user_id', $user->id)->first();

        if ($teacher) {
            return $teacher->branch_id;
        }

        $student = Student::where('user_id', $user->id)->first();

        if ($student) {
            return $student->branch_id;
        }

        return null;
    }

    private function isTeacher(): bool
    {
        return auth()->user()->roles()->where('title', 'Teacher')->exists();
    }

    private function isStudent(): bool
    {
        return auth()->user()->roles()->where('title', 'Student')->exists();
    }
}