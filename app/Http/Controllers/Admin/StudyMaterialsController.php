<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudyMaterialRequest;
use App\Http\Requests\UpdateStudyMaterialRequest;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\StudyMaterial;
use App\Models\Subject;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class StudyMaterialsController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('study_material_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $studyMaterials = StudyMaterial::with(['branch', 'course', 'batch', 'subject', 'uploadedBy'])
            ->latest()
            ->get();

        return view('admin.studyMaterials.index', compact('studyMaterials'));
    }

    public function create()
    {
        abort_if(Gate::denies('study_material_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branches = Branch::where('status', 'active')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $courses  = Course::where('status', 'active')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $batches  = Batch::where('status', 'active')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $subjects = Subject::where('status', 'active')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $users    = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $materialTypes = $this->materialTypes();

        return view('admin.studyMaterials.create', compact(
            'branches',
            'courses',
            'batches',
            'subjects',
            'users',
            'materialTypes'
        ));
    }

    public function store(StoreStudyMaterialRequest $request)
    {
        $data = $request->validated();

        if (empty($data['uploaded_by_id'])) {
            $data['uploaded_by_id'] = auth()->id();
        }

        $studyMaterial = StudyMaterial::create($data);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $studyMaterial->addMedia($file)->toMediaCollection('study_material_files');
            }
        }

        return redirect()->route('admin.study-materials.index')->with('message', 'Study material created successfully.');
    }

    public function show(StudyMaterial $studyMaterial)
    {
        abort_if(Gate::denies('study_material_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $studyMaterial->load(['branch', 'course', 'batch', 'subject', 'uploadedBy']);

        return view('admin.studyMaterials.show', compact('studyMaterial'));
    }

    public function edit(StudyMaterial $studyMaterial)
    {
        abort_if(Gate::denies('study_material_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branches = Branch::where('status', 'active')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $courses  = Course::where('status', 'active')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $batches  = Batch::where('status', 'active')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $subjects = Subject::where('status', 'active')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $users    = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $materialTypes = $this->materialTypes();

        $studyMaterial->load(['branch', 'course', 'batch', 'subject', 'uploadedBy']);

        return view('admin.studyMaterials.edit', compact(
            'studyMaterial',
            'branches',
            'courses',
            'batches',
            'subjects',
            'users',
            'materialTypes'
        ));
    }

    public function update(UpdateStudyMaterialRequest $request, StudyMaterial $studyMaterial)
    {
        $data = $request->validated();

        if (empty($data['uploaded_by_id'])) {
            $data['uploaded_by_id'] = $studyMaterial->uploaded_by_id ?? auth()->id();
        }

        $studyMaterial->update($data);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $studyMaterial->addMedia($file)->toMediaCollection('study_material_files');
            }
        }

        return redirect()->route('admin.study-materials.index')->with('message', 'Study material updated successfully.');
    }

    public function destroy(StudyMaterial $studyMaterial)
    {
        abort_if(Gate::denies('study_material_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $studyMaterial->delete();

        return back()->with('message', 'Study material deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('study_material_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        StudyMaterial::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function deleteMedia(Media $media)
    {
        abort_if(Gate::denies('study_material_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $media->delete();

        return back()->with('message', 'File deleted successfully.');
    }

    private function materialTypes(): array
    {
        return [
            'Notes'          => 'Notes',
            'PDF'            => 'PDF',
            'Assignment'     => 'Assignment',
            'Practice Paper' => 'Practice Paper',
            'Question Paper' => 'Question Paper',
            'Answer Key'     => 'Answer Key',
            'Video Link'     => 'Video Link',
            'Other'          => 'Other',
        ];
    }
}