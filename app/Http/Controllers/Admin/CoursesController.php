<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Branch;
use App\Models\Course;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CoursesController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('course_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branchId = $this->getUserBranchId();
        $courseId = $this->getStudentCourseId();

        $courses = Course::with(['branch'])
            ->when(auth()->user()->is_admin, function ($query) {
                return $query;
            })
            ->when(! auth()->user()->is_admin && $this->isStudent(), function ($query) use ($courseId) {
                $query->where('id', $courseId);
            })
            ->when(! auth()->user()->is_admin && ! $this->isStudent(), function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->latest()
            ->get();

        return view('admin.courses.index', compact('courses'));
    }

    public function create()
    {
        abort_if(Gate::denies('course_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        /*
         * Student ko create permission nahi dena chahiye.
         */
        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branchId = $this->getUserBranchId();

        $branches = Branch::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $query->where('id', $branchId);
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        return view('admin.courses.create', compact('branches'));
    }

    public function store(StoreCourseRequest $request)
    {
        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

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

        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkCourseAccess($course);

        $branchId = $this->getUserBranchId();

        $branches = Branch::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $query->where('id', $branchId);
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $course->load('branch');

        return view('admin.courses.edit', compact('course', 'branches'));
    }

    public function update(UpdateCourseRequest $request, Course $course)
    {
        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

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

        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkCourseAccess($course);

        $course->delete();

        return back()->with('message', 'Course deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('course_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branchId = $this->getUserBranchId();

        Course::whereIn('id', request('ids'))
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })
            ->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function checkCourseAccess(Course $course): void
    {
        if (auth()->user()->is_admin) {
            return;
        }

        if ($this->isStudent()) {
            $courseId = $this->getStudentCourseId();

            abort_if(! $courseId || $course->id != $courseId, Response::HTTP_FORBIDDEN, '403 Forbidden');

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

        $managedBranch = $user->managedBranch()->first();

        if ($managedBranch) {
            return $managedBranch->id;
        }

        $staff = $user->staffProfile()->first();

        if ($staff) {
            return $staff->branch_id;
        }

        $teacher = $user->teacherProfile()->first();

        if ($teacher) {
            return $teacher->branch_id;
        }

        $student = $user->studentProfile()->first();

        if ($student) {
            return $student->branch_id;
        }

        return null;
    }

    private function getStudentCourseId()
    {
        $student = auth()->user()->studentProfile()->first();

        return $student->course_id ?? null;
    }

    private function isStudent(): bool
    {
        return auth()->user()
            ->roles()
            ->where('title', 'Student')
            ->exists();
    }
}