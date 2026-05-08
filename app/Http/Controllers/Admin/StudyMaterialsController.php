<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudyMaterialRequest;
use App\Http\Requests\UpdateStudyMaterialRequest;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudyMaterial;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
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

        $studyMaterials = StudyMaterial::with(['branch', 'course', 'batch', 'subject', 'uploadedBy']);

        if (auth()->user()->is_admin) {
            // Admin all
        } elseif ($this->isStudent()) {
            $student = Student::where('user_id', auth()->id())->first();

            if ($student) {
                $studyMaterials->where(function ($query) use ($student) {
                    $query->where(function ($q) use ($student) {
                        $q->whereNull('branch_id')->orWhere('branch_id', $student->branch_id);
                    })
                    ->where(function ($q) use ($student) {
                        $q->whereNull('course_id')->orWhere('course_id', $student->course_id);
                    })
                    ->where(function ($q) use ($student) {
                        $q->whereNull('batch_id')->orWhere('batch_id', $student->batch_id);
                    });
                });
            } else {
                $studyMaterials->whereRaw('1 = 0');
            }
        } elseif ($this->isTeacher()) {
            $ids = $this->getTeacherAssignmentIds();

            if ($ids['course_ids']->count() || $ids['subject_ids']->count() || $ids['batch_ids']->count()) {
                $studyMaterials->where(function ($query) use ($ids) {
                    $query
                        ->when($ids['course_ids']->count(), function ($q) use ($ids) {
                            $q->whereIn('course_id', $ids['course_ids']);
                        })
                        ->when($ids['subject_ids']->count(), function ($q) use ($ids) {
                            $q->orWhereIn('subject_id', $ids['subject_ids']);
                        })
                        ->when($ids['batch_ids']->count(), function ($q) use ($ids) {
                            $q->orWhereIn('batch_id', $ids['batch_ids']);
                        });
                });
            } else {
                $studyMaterials->whereRaw('1 = 0');
            }
        } else {
            $branchId = $this->getUserBranchId();

            $branchId ? $studyMaterials->where('branch_id', $branchId) : $studyMaterials->whereRaw('1 = 0');
        }

        $studyMaterials = $studyMaterials->latest()->get();

        return view('admin.studyMaterials.index', compact('studyMaterials'));
    }

    public function create()
    {
        abort_if(Gate::denies('study_material_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branchId = $this->getUserBranchId();
        $ids = $this->getTeacherAssignmentIds();

        $branches = Branch::where('status', 'active')
            ->when(! auth()->user()->is_admin, fn ($q) => $branchId ? $q->where('id', $branchId) : $q->whereRaw('1 = 0'))
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $courses = Course::where('status', 'active')
            ->when(! auth()->user()->is_admin && ! $this->isTeacher(), fn ($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereRaw('1 = 0'))
            ->when($this->isTeacher(), fn ($q) => $ids['course_ids']->count() ? $q->whereIn('id', $ids['course_ids']) : $q->whereRaw('1 = 0'))
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $batches = Batch::where('status', 'active')
            ->when(! auth()->user()->is_admin && ! $this->isTeacher(), fn ($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereRaw('1 = 0'))
            ->when($this->isTeacher(), fn ($q) => $ids['batch_ids']->count() ? $q->whereIn('id', $ids['batch_ids']) : $q->whereRaw('1 = 0'))
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $subjects = Subject::where('status', 'active')
            ->when(! auth()->user()->is_admin && ! $this->isTeacher(), fn ($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereRaw('1 = 0'))
            ->when($this->isTeacher(), fn ($q) => $ids['subject_ids']->count() ? $q->whereIn('id', $ids['subject_ids']) : $q->whereRaw('1 = 0'))
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

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
        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validated();

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;

            if ($this->isTeacher()) {
                $this->validateTeacherMaterialData($data);
            } else {
                $this->validateBranchAcademicData($data, $branchId);
            }
        }

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

        $this->checkStudyMaterialAccess($studyMaterial);

        $studyMaterial->load(['branch', 'course', 'batch', 'subject', 'uploadedBy']);

        return view('admin.studyMaterials.show', compact('studyMaterial'));
    }

    public function edit(StudyMaterial $studyMaterial)
    {
        abort_if(Gate::denies('study_material_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkStudyMaterialAccess($studyMaterial);

        $branchId = $this->getUserBranchId();
        $ids = $this->getTeacherAssignmentIds();

        $branches = Branch::where('status', 'active')
            ->when(! auth()->user()->is_admin, fn ($q) => $branchId ? $q->where('id', $branchId) : $q->whereRaw('1 = 0'))
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $courses = Course::where('status', 'active')
            ->when(! auth()->user()->is_admin && ! $this->isTeacher(), fn ($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereRaw('1 = 0'))
            ->when($this->isTeacher(), fn ($q) => $ids['course_ids']->count() ? $q->whereIn('id', $ids['course_ids']) : $q->whereRaw('1 = 0'))
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $batches = Batch::where('status', 'active')
            ->when(! auth()->user()->is_admin && ! $this->isTeacher(), fn ($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereRaw('1 = 0'))
            ->when($this->isTeacher(), fn ($q) => $ids['batch_ids']->count() ? $q->whereIn('id', $ids['batch_ids']) : $q->whereRaw('1 = 0'))
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $subjects = Subject::where('status', 'active')
            ->when(! auth()->user()->is_admin && ! $this->isTeacher(), fn ($q) => $branchId ? $q->where('branch_id', $branchId) : $q->whereRaw('1 = 0'))
            ->when($this->isTeacher(), fn ($q) => $ids['subject_ids']->count() ? $q->whereIn('id', $ids['subject_ids']) : $q->whereRaw('1 = 0'))
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

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
        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkStudyMaterialAccess($studyMaterial);

        $data = $request->validated();

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;

            if ($this->isTeacher()) {
                $this->validateTeacherMaterialData($data);
            } else {
                $this->validateBranchAcademicData($data, $branchId);
            }
        }

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

        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkStudyMaterialAccess($studyMaterial);

        $studyMaterial->delete();

        return back()->with('message', 'Study material deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('study_material_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = StudyMaterial::whereIn('id', request('ids'));

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
        }

        $query->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function deleteMedia(Media $media)
    {
        abort_if(Gate::denies('study_material_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $studyMaterial = $media->model;

        if ($studyMaterial instanceof StudyMaterial) {
            $this->checkStudyMaterialAccess($studyMaterial);
        }

        $media->delete();

        return back()->with('message', 'File deleted successfully.');
    }

    private function checkStudyMaterialAccess(StudyMaterial $studyMaterial): void
    {
        if (auth()->user()->is_admin) {
            return;
        }

        if ($this->isStudent()) {
            $student = Student::where('user_id', auth()->id())->first();

            abort_if(
                ! $student ||
                ($studyMaterial->branch_id && $studyMaterial->branch_id != $student->branch_id) ||
                ($studyMaterial->course_id && $studyMaterial->course_id != $student->course_id) ||
                ($studyMaterial->batch_id && $studyMaterial->batch_id != $student->batch_id),
                Response::HTTP_FORBIDDEN,
                '403 Forbidden'
            );

            return;
        }

        if ($this->isTeacher()) {
            $ids = $this->getTeacherAssignmentIds();

            $allowed =
                ($studyMaterial->course_id && $ids['course_ids']->contains($studyMaterial->course_id)) ||
                ($studyMaterial->subject_id && $ids['subject_ids']->contains($studyMaterial->subject_id)) ||
                ($studyMaterial->batch_id && $ids['batch_ids']->contains($studyMaterial->batch_id));

            abort_if(! $allowed, Response::HTTP_FORBIDDEN, '403 Forbidden');

            return;
        }

        $branchId = $this->getUserBranchId();

        abort_if(! $branchId || $studyMaterial->branch_id != $branchId, Response::HTTP_FORBIDDEN, '403 Forbidden');
    }

    private function validateBranchAcademicData(array $data, $branchId): void
    {
        if (! empty($data['course_id'])) {
            abort_if(! Course::where('id', $data['course_id'])->where('branch_id', $branchId)->exists(), Response::HTTP_FORBIDDEN, 'Invalid course.');
        }

        if (! empty($data['batch_id'])) {
            abort_if(! Batch::where('id', $data['batch_id'])->where('branch_id', $branchId)->exists(), Response::HTTP_FORBIDDEN, 'Invalid batch.');
        }

        if (! empty($data['subject_id'])) {
            abort_if(! Subject::where('id', $data['subject_id'])->where('branch_id', $branchId)->exists(), Response::HTTP_FORBIDDEN, 'Invalid subject.');
        }
    }

    private function validateTeacherMaterialData(array $data): void
    {
        $ids = $this->getTeacherAssignmentIds();

        if (! empty($data['course_id'])) {
            abort_if(! $ids['course_ids']->contains((int) $data['course_id']), Response::HTTP_FORBIDDEN, 'Invalid course.');
        }

        if (! empty($data['batch_id'])) {
            abort_if(! $ids['batch_ids']->contains((int) $data['batch_id']), Response::HTTP_FORBIDDEN, 'Invalid batch.');
        }

        if (! empty($data['subject_id'])) {
            abort_if(! $ids['subject_ids']->contains((int) $data['subject_id']), Response::HTTP_FORBIDDEN, 'Invalid subject.');
        }
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

    private function getTeacherAssignmentIds(): array
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();

        if (! $teacher) {
            return [
                'course_ids'  => collect([]),
                'subject_ids' => collect([]),
                'batch_ids'   => collect([]),
            ];
        }

        $assignments = TeacherAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->get();

        return [
            'course_ids'  => $assignments->pluck('course_id')->filter()->unique()->values(),
            'subject_ids' => $assignments->pluck('subject_id')->filter()->unique()->values(),
            'batch_ids'   => $assignments->pluck('batch_id')->filter()->unique()->values(),
        ];
    }

    private function isTeacher(): bool
    {
        return auth()->user()->roles()->where('title', 'Teacher')->exists();
    }

    private function isStudent(): bool
    {
        return auth()->user()->roles()->where('title', 'Student')->exists();
    }
}