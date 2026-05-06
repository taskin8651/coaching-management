<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\Branch;
use App\Models\Teacher;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeachersController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('teacher_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $teachers = Teacher::with(['user', 'branch'])->latest()->get();

        return view('admin.teachers.index', compact('teachers'));
    }

    public function create()
    {
        abort_if(Gate::denies('teacher_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $users = User::whereHas('roles', function ($query) {
                $query->where('title', 'Teacher');
            })
            ->whereDoesntHave('teacher')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $branches = Branch::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        return view('admin.teachers.create', compact('users', 'branches'));
    }

    public function store(StoreTeacherRequest $request)
    {
        $teacher = Teacher::create($request->validated());

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

        $teacher->load(['user', 'branch']);

        return view('admin.teachers.show', compact('teacher'));
    }

    public function edit(Teacher $teacher)
    {
        abort_if(Gate::denies('teacher_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $users = User::whereHas('roles', function ($query) {
                $query->where('title', 'Teacher');
            })
            ->where(function ($query) use ($teacher) {
                $query->whereDoesntHave('teacher')
                    ->orWhere('id', $teacher->user_id);
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $branches = Branch::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $teacher->load(['user', 'branch']);

        return view('admin.teachers.edit', compact('teacher', 'users', 'branches'));
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        $teacher->update($request->validated());

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

        $teacher->delete();

        return back()->with('message', 'Teacher deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('teacher_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Teacher::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}