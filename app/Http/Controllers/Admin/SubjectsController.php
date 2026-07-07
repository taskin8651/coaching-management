<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubjectsController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('subject_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subjects = Subject::with(['branch', 'course']);

        if (auth()->user()->is_admin) {
            // Admin ko all subjects
        } elseif ($this->isStudent()) {
            $courseId = $this->getStudentCourseId();

            if ($courseId) {
                $subjects->where('course_id', $courseId);
            } else {
                $subjects->whereRaw('1 = 0');
            }
        } elseif ($this->isTeacher()) {
            $subjectIds = $this->getTeacherSubjectIds();

            if ($subjectIds->count()) {
                $subjects->whereIn('id', $subjectIds);
            } else {
                $subjects->whereRaw('1 = 0');
            }
        } else {
            $branchId = $this->getUserBranchId();

            if ($branchId) {
                $subjects->where('branch_id', $branchId);
            } else {
                $subjects->whereRaw('1 = 0');
            }
        }

        $subjects = $subjects->latest()->get();

        return view('admin.subjects.index', compact('subjects'));
    }

    public function create()
    {
        abort_if(Gate::denies('subject_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

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

        $coursesByBranch = $this->coursesByBranch($branchId);
        $defaultBranchId = auth()->user()->is_admin ? null : $branchId;

        return view('admin.subjects.create', compact('branches', 'courses', 'coursesByBranch', 'defaultBranchId'));
    }

    public function store(StoreSubjectRequest $request)
    {
        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validated();

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;
        }

        $this->validateCourseBranch($data);

        Subject::create($data);

        return redirect()->route('admin.subjects.index')->with('message', 'Subject created successfully.');
    }

    public function show(Subject $subject)
    {
        abort_if(Gate::denies('subject_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkSubjectAccess($subject);

        $subject->load(['branch', 'course']);

        return view('admin.subjects.show', compact('subject'));
    }

    public function edit(Subject $subject)
    {
        abort_if(Gate::denies('subject_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkSubjectAccess($subject);

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

        $coursesByBranch = $this->coursesByBranch($branchId);

        $subject->load(['branch', 'course']);

        return view('admin.subjects.edit', compact('subject', 'branches', 'courses', 'coursesByBranch'));
    }

    public function update(UpdateSubjectRequest $request, Subject $subject)
    {
        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkSubjectAccess($subject);

        $data = $request->validated();

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;
        }

        $this->validateCourseBranch($data);

        $subject->update($data);

        return redirect()->route('admin.subjects.index')->with('message', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject)
    {
        abort_if(Gate::denies('subject_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkSubjectAccess($subject);

        $subject->delete();

        return back()->with('message', 'Subject deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('subject_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Subject::whereIn('id', request('ids'));

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

    private function checkSubjectAccess(Subject $subject): void
    {
        if (auth()->user()->is_admin) {
            return;
        }

        if ($this->isStudent()) {
            $courseId = $this->getStudentCourseId();

            abort_if(! $courseId || $subject->course_id != $courseId, Response::HTTP_FORBIDDEN, '403 Forbidden');

            return;
        }

        if ($this->isTeacher()) {
            $subjectIds = $this->getTeacherSubjectIds();

            abort_if(! $subjectIds->contains($subject->id), Response::HTTP_FORBIDDEN, '403 Forbidden');

            return;
        }

        $branchId = $this->getUserBranchId();

        abort_if(! $branchId || $subject->branch_id != $branchId, Response::HTTP_FORBIDDEN, '403 Forbidden');
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

    private function getStudentCourseId()
    {
        $student = Student::where('user_id', auth()->id())->first();

        return $student->course_id ?? null;
    }

    private function getTeacherSubjectIds()
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();

        if (! $teacher) {
            return collect([]);
        }

        return TeacherAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->whereNotNull('subject_id')
            ->pluck('subject_id')
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

    private function coursesByBranch($branchId = null): array
    {
        return Course::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->orderBy('name')
            ->get(['id', 'name', 'branch_id'])
            ->groupBy('branch_id')
            ->map(fn ($courses) => $courses->map(fn ($course) => [
                'id' => $course->id,
                'name' => $course->name,
            ])->values())
            ->toArray();
    }

    private function validateCourseBranch(array $data): void
    {
        if (empty($data['course_id'])) {
            return;
        }

        $course = Course::find($data['course_id']);

        abort_if(! $course, Response::HTTP_UNPROCESSABLE_ENTITY, 'Invalid course selected.');

        if (! empty($data['branch_id'])) {
            abort_if((int) $course->branch_id !== (int) $data['branch_id'], Response::HTTP_UNPROCESSABLE_ENTITY, 'Selected course does not belong to selected branch.');
        }
    }
}
