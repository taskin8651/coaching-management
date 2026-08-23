<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExamTypeRequest;
use App\Http\Requests\UpdateExamTypeRequest;
use App\Models\ExamType;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExamTypesController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('exam_type_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $examTypes = ExamType::latest()->get();

        return view('admin.exam-types.index', compact('examTypes'));
    }

    public function create()
    {
        abort_if(Gate::denies('exam_type_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.exam-types.create');
    }

    public function store(StoreExamTypeRequest $request)
    {
        $examType = ExamType::create($request->validated());

        return redirect()->route('admin.exam-types.index')->with('message', 'Exam type created successfully.');
    }

    public function show(ExamType $examType)
    {
        abort_if(Gate::denies('exam_type_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.exam-types.show', compact('examType'));
    }

    public function edit(ExamType $examType)
    {
        abort_if(Gate::denies('exam_type_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.exam-types.edit', compact('examType'));
    }

    public function update(UpdateExamTypeRequest $request, ExamType $examType)
    {
        $examType->update($request->validated());

        return redirect()->route('admin.exam-types.index')->with('message', 'Exam type updated successfully.');
    }

    public function destroy(ExamType $examType)
    {
        abort_if(Gate::denies('exam_type_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $examType->delete();

        return back()->with('message', 'Exam type deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('exam_type_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        ExamType::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
