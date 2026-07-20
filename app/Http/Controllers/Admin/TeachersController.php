<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\SyncsProfileUser;
use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;
use Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeachersController extends Controller
{
    use AppliesErpScope;
    use SyncsProfileUser;

    public function index()
    {
        abort_if(Gate::denies('teacher_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branchId = $this->getUserBranchId();

        $teachers = Teacher::with([
                'user',
                'branch',
                'assignments.course',
                'assignments.subject',
                'assignments.batch',
            ])
            ->when(auth()->user()->is_admin, function ($query) {
                return $query;
            })
            ->when(! auth()->user()->is_admin && $this->isTeacher(), function ($query) {
                $teacher = Teacher::where('user_id', auth()->id())->first();

                $query->where('id', $teacher->id ?? 0);
            })
            ->when(! auth()->user()->is_admin && ! $this->isTeacher(), function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->latest()
            ->get();

        return view('admin.teachers.index', compact('teachers'));
    }

    public function create()
    {
        abort_if(Gate::denies('teacher_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branchId = $this->getUserBranchId();

        ['users' => $users, 'userDetails' => $userDetails] = $this->profileUserSelectData('Teacher', 'teacherProfile');

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
            ->prepend('Select Course', '');

        $subjects = Subject::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->pluck('name', 'id')
            ->prepend('Select Subject', '');

        $batches = Batch::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->pluck('name', 'id')
            ->prepend('Select Batch', '');

        $coursesByBranch = $this->coursesByBranch();
        $batchesByBranch = $this->batchesByBranch();
        $subjectsByBranch = $this->subjectsByBranch();

        return view('admin.teachers.create', compact(
            'users',
            'userDetails',
            'branches',
            'courses',
            'subjects',
            'batches',
            'coursesByBranch',
            'batchesByBranch',
            'subjectsByBranch'
        ));
    }

    public function store(StoreTeacherRequest $request)
    {
        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validated();

        unset($data['course_ids'], $data['subject_ids'], $data['batch_ids']);

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;
        }

        $teacher = DB::transaction(function () use ($data) {
            $user = $this->syncProfileUser($data, 'Teacher');
            $data['user_id'] = $user->id;

            return Teacher::create($this->profileData($data));
        });

        $this->syncAssignments($teacher, $request);

        if ($request->hasFile('photo')) {
            $teacher->addMediaFromRequest('photo')->toMediaCollection('teacher_photo');
        }

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $teacher->addMedia($document)->toMediaCollection('teacher_documents');
            }
        }

        return redirect()->route('admin.teachers.index')->with('message', 'Teacher created successfully.');
    }

    public function show(Teacher $teacher)
    {
        abort_if(Gate::denies('teacher_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkTeacherAccess($teacher);

        $teacher->load([
            'user',
            'branch',
            'assignments.course',
            'assignments.subject',
            'assignments.batch',
            'salaryPayments',
        ]);

        return view('admin.teachers.show', compact('teacher'));
    }

    public function edit(Teacher $teacher)
    {
        abort_if(Gate::denies('teacher_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkTeacherAccess($teacher);

        $branchId = $this->getUserBranchId();

        ['users' => $users, 'userDetails' => $userDetails] = $this->profileUserSelectData('Teacher', 'teacherProfile', $teacher->user_id);

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
            ->prepend('Select Course', '');

        $subjects = Subject::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->pluck('name', 'id')
            ->prepend('Select Subject', '');

        $batches = Batch::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->pluck('name', 'id')
            ->prepend('Select Batch', '');

        $teacher->load(['user', 'branch', 'assignments']);

        $assignments = $teacher->assignments->values();
        $coursesByBranch = $this->coursesByBranch();
        $batchesByBranch = $this->batchesByBranch();
        $subjectsByBranch = $this->subjectsByBranch();

        return view('admin.teachers.edit', compact(
            'teacher',
            'users',
            'userDetails',
            'branches',
            'courses',
            'subjects',
            'batches',
            'assignments',
            'coursesByBranch',
            'batchesByBranch',
            'subjectsByBranch'
        ));
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkTeacherAccess($teacher);

        $data = $request->validated();

        unset($data['course_ids'], $data['subject_ids'], $data['batch_ids']);

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;
        }

        DB::transaction(function () use ($teacher, $data) {
            $data['user_id'] = $data['user_id'] ?? $teacher->user_id;

            $user = $this->syncProfileUser($data, 'Teacher');
            $data['user_id'] = $user->id;

            $teacher->update($this->profileData($data));
        });

        $this->syncAssignments($teacher, $request);

        if ($request->hasFile('photo')) {
            $teacher->clearMediaCollection('teacher_photo');
            $teacher->addMediaFromRequest('photo')->toMediaCollection('teacher_photo');
        }

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $teacher->addMedia($document)->toMediaCollection('teacher_documents');
            }
        }

        return redirect()->route('admin.teachers.index')->with('message', 'Teacher updated successfully.');
    }

    public function destroy(Teacher $teacher)
    {
        abort_if(Gate::denies('teacher_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkTeacherAccess($teacher);

        $teacher->delete();

        return back()->with('message', 'Teacher deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('teacher_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branchId = $this->getUserBranchId();

        Teacher::whereIn('id', request('ids'))
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function syncAssignments(Teacher $teacher, Request $request): void
    {
        TeacherAssignment::where('teacher_id', $teacher->id)->delete();

        $courseIds  = $request->input('course_ids', []);
        $subjectIds = $request->input('subject_ids', []);
        $batchIds   = $request->input('batch_ids', []);

        $max = max(count($courseIds), count($subjectIds), count($batchIds));

        for ($i = 0; $i < $max; $i++) {
            $courseId  = $courseIds[$i] ?? null;
            $subjectId = $subjectIds[$i] ?? null;
            $batchId   = $batchIds[$i] ?? null;

            if (! $courseId && ! $subjectId && ! $batchId) {
                continue;
            }

            TeacherAssignment::create([
                'teacher_id' => $teacher->id,
                'branch_id'  => $teacher->branch_id,
                'course_id'  => $courseId ?: null,
                'subject_id' => $subjectId ?: null,
                'batch_id'   => $batchId ?: null,
                'status'     => 'active',
            ]);
        }
    }

    private function checkTeacherAccess(Teacher $teacher): void
    {
        if (auth()->user()->is_admin) {
            return;
        }

        if ($this->isTeacher()) {
            $authTeacher = Teacher::where('user_id', auth()->id())->first();

            abort_if(! $authTeacher || $teacher->id != $authTeacher->id, Response::HTTP_FORBIDDEN, '403 Forbidden');

            return;
        }

        $branchId = $this->getUserBranchId();

        abort_if(! $branchId || $teacher->branch_id != $branchId, Response::HTTP_FORBIDDEN, '403 Forbidden');
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
        return auth()->user()
            ->roles()
            ->where('title', 'Teacher')
            ->exists();
    }

    private function isStudent(): bool
    {
        return auth()->user()
            ->roles()
            ->where('title', 'Student')
            ->exists();
    }
}
