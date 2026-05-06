<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Subject;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubjectsController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('subject_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subjects = Subject::with(['branch', 'course'])->latest()->get();

        return view('admin.subjects.index', compact('subjects'));
    }

    public function create()
    {
        abort_if(Gate::denies('subject_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branches = Branch::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $courses = Course::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        return view('admin.subjects.create', compact('branches', 'courses'));
    }

    public function store(StoreSubjectRequest $request)
    {
        Subject::create($request->validated());

        return redirect()->route('admin.subjects.index')->with('message', 'Subject created successfully.');
    }

    public function show(Subject $subject)
    {
        abort_if(Gate::denies('subject_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subject->load(['branch', 'course']);

        return view('admin.subjects.show', compact('subject'));
    }

    public function edit(Subject $subject)
    {
        abort_if(Gate::denies('subject_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branches = Branch::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $courses = Course::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $subject->load(['branch', 'course']);

        return view('admin.subjects.edit', compact('subject', 'branches', 'courses'));
    }

    public function update(UpdateSubjectRequest $request, Subject $subject)
    {
        $subject->update($request->validated());

        return redirect()->route('admin.subjects.index')->with('message', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject)
    {
        abort_if(Gate::denies('subject_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $subject->delete();

        return back()->with('message', 'Subject deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('subject_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Subject::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}