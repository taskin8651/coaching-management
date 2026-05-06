<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StudentsController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('student_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $students = Student::with(['user', 'branch', 'course', 'batch'])->latest()->get();

        return view('admin.students.index', compact('students'));
    }

    public function create()
    {
        abort_if(Gate::denies('student_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $users = User::whereHas('roles', function ($query) {
                $query->where('title', 'Student');
            })
            ->whereDoesntHave('student')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $branches = Branch::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $courses = Course::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $batches = Batch::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        return view('admin.students.create', compact('users', 'branches', 'courses', 'batches'));
    }

    public function store(StoreStudentRequest $request)
    {
        $student = Student::create($request->validated());

        if ($request->hasFile('photo')) {
            $student->addMediaFromRequest('photo')->toMediaCollection('student_photo');
        }

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $student->addMedia($document)->toMediaCollection('student_documents');
            }
        }

        return redirect()->route('admin.students.index')->with('message', 'Student created successfully.');
    }

    public function show(Student $student)
    {
        abort_if(Gate::denies('student_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $student->load(['user', 'branch', 'course', 'batch']);

        return view('admin.students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        abort_if(Gate::denies('student_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $users = User::whereHas('roles', function ($query) {
                $query->where('title', 'Student');
            })
            ->where(function ($query) use ($student) {
                $query->whereDoesntHave('student')
                    ->orWhere('id', $student->user_id);
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $branches = Branch::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $courses = Course::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $batches = Batch::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $student->load(['user', 'branch', 'course', 'batch']);

        return view('admin.students.edit', compact('student', 'users', 'branches', 'courses', 'batches'));
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $student->update($request->validated());

        if ($request->hasFile('photo')) {
            $student->clearMediaCollection('student_photo');
            $student->addMediaFromRequest('photo')->toMediaCollection('student_photo');
        }

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $student->addMedia($document)->toMediaCollection('student_documents');
            }
        }

        return redirect()->route('admin.students.index')->with('message', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        abort_if(Gate::denies('student_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $student->delete();

        return back()->with('message', 'Student deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('student_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Student::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}