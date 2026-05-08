<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CoursesController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('course_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = auth()->user();

        $courses = Course::with(['branch']);

        if ($user->is_admin) {
            // Admin ko sab courses
        } elseif ($this->isStudent()) {
            // Student ko sirf apna course
            $courseId = $this->getStudentCourseId();

            if ($courseId) {
                $courses->where('id', $courseId);
            } else {
                $courses->whereRaw('1 = 0');
            }
        } elseif ($this->isTeacher()) {
            // Teacher ko sirf assigned courses
            $courseIds = $this->getTeacherCourseIds();

            if ($courseIds->count()) {
                $courses->whereIn('id', $courseIds);
            } else {
                $courses->whereRaw('1 = 0');
            }
        } else {
            // Branch Manager / Staff ko apni branch ke courses
            $branchId = $this->getUserBranchId();

            if ($branchId) {
                $courses->where('branch_id', $branchId);
            } else {
                $courses->whereRaw('1 = 0');
            }
        }

        $courses = $courses->latest()->get();

        return view('admin.courses.index', compact('courses'));
    }

    public function create()
    {
        abort_if(Gate::denies('course_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Student aur Teacher ko course create allow nahi
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

        return view('admin.courses.create', compact('branches'));
    }

    public function store(StoreCourseRequest $request)
    {
        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validated();

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;
        }

        $course = Course::create($data);

        if ($request->hasFile('image')) {
            $course->addMediaFromRequest('image')->toMediaCollection('course_image');
        }

        return redirect()->route('admin.courses.index')->with('message', 'Course created successfully.');
    }

    public function show(Course $course)
    {
        abort_if(Gate::denies('course_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkCourseAccess($course);

        $course->load('branch');

        return view('admin.courses.show', compact('course'));
    }

    public function edit(Course $course)
    {
        abort_if(Gate::denies('course_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Student aur Teacher ko course edit allow nahi
        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkCourseAccess($course);

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

        $course->load('branch');

        return view('admin.courses.edit', compact('course', 'branches'));
    }

    public function update(UpdateCourseRequest $request, Course $course)
    {
        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkCourseAccess($course);

        $data = $request->validated();

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;
        }

        $course->update($data);

        if ($request->hasFile('image')) {
            $course->clearMediaCollection('course_image');
            $course->addMediaFromRequest('image')->toMediaCollection('course_image');
        }

        return redirect()->route('admin.courses.index')->with('message', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        abort_if(Gate::denies('course_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkCourseAccess($course);

        $course->delete();

        return back()->with('message', 'Course deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('course_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user = auth()->user();

        $query = Course::whereIn('id', request('ids'));

        if (! $user->is_admin) {
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

    private function checkCourseAccess(Course $course): void
    {
        $user = auth()->user();

        if ($user->is_admin) {
            return;
        }

        if ($this->isStudent()) {
            $courseId = $this->getStudentCourseId();

            abort_if(! $courseId || $course->id != $courseId, Response::HTTP_FORBIDDEN, '403 Forbidden');

            return;
        }

        if ($this->isTeacher()) {
            $courseIds = $this->getTeacherCourseIds();

            abort_if(! $courseIds->contains($course->id), Response::HTTP_FORBIDDEN, '403 Forbidden');

            return;
        }

        $branchId = $this->getUserBranchId();

        abort_if(! $branchId || $course->branch_id != $branchId, Response::HTTP_FORBIDDEN, '403 Forbidden');
    }

    private function getUserBranchId()
    {
        $user = auth()->user();

        if ($user->is_admin) {
            return null;
        }

        // Branch Manager: branches.manager_id = users.id
        $managedBranch = Branch::where('manager_id', $user->id)->first();

        if ($managedBranch) {
            return $managedBranch->id;
        }

        // Staff
        $staff = Staff::where('user_id', $user->id)->first();

        if ($staff) {
            return $staff->branch_id;
        }

        // Teacher
        $teacher = Teacher::where('user_id', $user->id)->first();

        if ($teacher) {
            return $teacher->branch_id;
        }

        // Student
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

    private function getTeacherCourseIds()
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();

        if (! $teacher) {
            return collect([]);
        }

        return TeacherAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->whereNotNull('course_id')
            ->pluck('course_id')
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
}