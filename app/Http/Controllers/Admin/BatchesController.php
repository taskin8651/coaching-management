<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBatchRequest;
use App\Http\Requests\UpdateBatchRequest;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class BatchesController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('batch_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $batches = Batch::with(['branch', 'course']);

        if (auth()->user()->is_admin) {
            // Admin ko all batches
        } elseif ($this->isStudent()) {
            $batchId = $this->getStudentBatchId();

            if ($batchId) {
                $batches->where('id', $batchId);
            } else {
                $batches->whereRaw('1 = 0');
            }
        } elseif ($this->isTeacher()) {
            $batchIds = $this->getTeacherBatchIds();

            if ($batchIds->count()) {
                $batches->whereIn('id', $batchIds);
            } else {
                $batches->whereRaw('1 = 0');
            }
        } else {
            $branchId = $this->getUserBranchId();

            if ($branchId) {
                $batches->where('branch_id', $branchId);
            } else {
                $batches->whereRaw('1 = 0');
            }
        }

        $batches = $batches->latest()->get();

        return view('admin.batches.index', compact('batches'));
    }

    public function create()
    {
        abort_if(Gate::denies('batch_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branchId = $this->getUserBranchId();

        $branches = Branch::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where('id', $branchId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $courses = Course::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $subjects = $this->subjectOptions($branchId);

        return view('admin.batches.create', compact('branches', 'courses', 'subjects'));
    }

    public function store(StoreBatchRequest $request)
    {
        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validated();

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;

            if (! empty($data['course_id'])) {
                $course = Course::where('id', $data['course_id'])
                    ->where('branch_id', $branchId)
                    ->first();

                abort_if(! $course, Response::HTTP_FORBIDDEN, 'Invalid course for your branch.');
            }
        }

        $subjectIds = $data['subject_ids'] ?? [];
        unset($data['subject_ids']);

        $this->validateSubjectAccess($subjectIds, $data['branch_id'] ?? null, $data['course_id'] ?? null);

        DB::transaction(function () use ($data, $subjectIds) {
            $batch = Batch::create($data);
            $batch->subjects()->sync($subjectIds);
        });

        return redirect()->route('admin.batches.index')->with('message', 'Batch created successfully.');
    }

    public function show(Batch $batch)
    {
        abort_if(Gate::denies('batch_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkBatchAccess($batch);

        $batch->load(['branch', 'course']);

        return view('admin.batches.show', compact('batch'));
    }

    public function edit(Batch $batch)
    {
        abort_if(Gate::denies('batch_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkBatchAccess($batch);

        $branchId = $this->getUserBranchId();

        $branches = Branch::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where('id', $branchId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $courses = Course::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $subjects = $this->subjectOptions($branchId);

        $batch->load(['branch', 'course', 'subjects']);

        return view('admin.batches.edit', compact('batch', 'branches', 'courses', 'subjects'));
    }

    public function update(UpdateBatchRequest $request, Batch $batch)
    {
        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkBatchAccess($batch);

        $data = $request->validated();

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;

            if (! empty($data['course_id'])) {
                $course = Course::where('id', $data['course_id'])
                    ->where('branch_id', $branchId)
                    ->first();

                abort_if(! $course, Response::HTTP_FORBIDDEN, 'Invalid course for your branch.');
            }
        }

        $subjectIds = $data['subject_ids'] ?? [];
        unset($data['subject_ids']);

        $this->validateSubjectAccess($subjectIds, $data['branch_id'] ?? null, $data['course_id'] ?? null);

        DB::transaction(function () use ($batch, $data, $subjectIds) {
            $batch->update($data);
            $batch->subjects()->sync($subjectIds);
        });

        return redirect()->route('admin.batches.index')->with('message', 'Batch updated successfully.');
    }

    public function destroy(Batch $batch)
    {
        abort_if(Gate::denies('batch_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkBatchAccess($batch);

        $batch->delete();

        return back()->with('message', 'Batch deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('batch_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Batch::whereIn('id', request('ids'));

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            if ($branchId) {
                $query->where('branch_id', $branchId);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $query->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function checkBatchAccess(Batch $batch): void
    {
        if (auth()->user()->is_admin) {
            return;
        }

        if ($this->isStudent()) {
            $batchId = $this->getStudentBatchId();

            abort_if(! $batchId || $batch->id != $batchId, Response::HTTP_FORBIDDEN, '403 Forbidden');

            return;
        }

        if ($this->isTeacher()) {
            $batchIds = $this->getTeacherBatchIds();

            abort_if(! $batchIds->contains($batch->id), Response::HTTP_FORBIDDEN, '403 Forbidden');

            return;
        }

        $branchId = $this->getUserBranchId();

        abort_if(! $branchId || $batch->branch_id != $branchId, Response::HTTP_FORBIDDEN, '403 Forbidden');
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

    private function getStudentBatchId()
    {
        $student = Student::where('user_id', auth()->id())->first();

        return $student->batch_id ?? null;
    }

    private function getTeacherBatchIds()
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();

        if (! $teacher) {
            return collect([]);
        }

        return TeacherAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->whereNotNull('batch_id')
            ->pluck('batch_id')
            ->unique()
            ->values();
    }

    private function isStudent(): bool
    {
        return auth()->user()
            ->roles()
            ->where('title', 'Student')
            ->exists();
    }

    private function isTeacher(): bool
    {
        return auth()->user()
            ->roles()
            ->where('title', 'Teacher')
            ->exists();
    }

    private function subjectOptions($branchId)
    {
        return Subject::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->pluck('name', 'id');
    }

    private function validateSubjectAccess(array $subjectIds, $branchId, $courseId): void
    {
        if (empty($subjectIds)) {
            return;
        }

        $query = Subject::whereIn('id', $subjectIds);

        if (! auth()->user()->is_admin) {
            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');
            $query->where('branch_id', $branchId);
        }

        if ($courseId) {
            $query->where(function ($q) use ($courseId) {
                $q->whereNull('course_id')
                    ->orWhere('course_id', $courseId);
            });
        }

        abort_if($query->count() !== count(array_unique($subjectIds)), Response::HTTP_FORBIDDEN, 'Invalid subject for selected batch.');
    }
}
