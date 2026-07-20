<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNoticeRequest;
use App\Http\Requests\UpdateNoticeRequest;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Notice;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class NoticesController extends Controller
{
    use AppliesErpScope;
    public function index()
    {
        abort_if(Gate::denies('notice_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $notices = Notice::with(['branch', 'course', 'batch', 'createdBy']);

        if (auth()->user()->is_admin) {
            // Admin all notices
        } elseif ($this->isStudent()) {
            $student = Student::where('user_id', auth()->id())->first();

            if ($student) {
                $notices->where(function ($query) use ($student) {
                    $query->where('target_audience', 'all')
                        ->orWhere('target_audience', 'students')
                        ->orWhere(function ($q) use ($student) {
                            $q->where('target_audience', 'branch')
                                ->where('branch_id', $student->branch_id);
                        })
                        ->orWhere(function ($q) use ($student) {
                            $q->where('target_audience', 'course')
                                ->where('course_id', $student->course_id);
                        })
                        ->orWhere(function ($q) use ($student) {
                            $q->where('target_audience', 'batch')
                                ->where('batch_id', $student->batch_id);
                        });
                });
            } else {
                $notices->whereRaw('1 = 0');
            }
        } elseif ($this->isTeacher()) {
            $ids = $this->getTeacherAssignmentIds();
            $branchId = $this->getUserBranchId();

            $notices->where(function ($query) use ($ids, $branchId) {
                $query->where('target_audience', 'all')
                    ->orWhere('target_audience', 'teachers')
                    ->orWhere(function ($q) use ($branchId) {
                        $q->where('target_audience', 'branch')
                            ->where('branch_id', $branchId);
                    })
                    ->when($ids['course_ids']->count(), function ($q) use ($ids) {
                        $q->orWhere(function ($qq) use ($ids) {
                            $qq->where('target_audience', 'course')
                                ->whereIn('course_id', $ids['course_ids']);
                        });
                    })
                    ->when($ids['batch_ids']->count(), function ($q) use ($ids) {
                        $q->orWhere(function ($qq) use ($ids) {
                            $qq->where('target_audience', 'batch')
                                ->whereIn('batch_id', $ids['batch_ids']);
                        });
                    });
            });
        } elseif ($this->isStaff()) {
            $branchId = $this->getUserBranchId();

            if ($branchId) {
                $notices->where(function ($query) use ($branchId) {
                    $query->where('target_audience', 'all')
                        ->orWhere('target_audience', 'staff')
                        ->orWhere(function ($q) use ($branchId) {
                            $q->where('target_audience', 'branch')
                                ->where('branch_id', $branchId);
                        });
                });
            } else {
                $notices->whereRaw('1 = 0');
            }
        } else {
            // Branch Manager
            $branchId = $this->getUserBranchId();

            if ($branchId) {
                $notices->where(function ($query) use ($branchId) {
                    $query->where('target_audience', 'all')
                        ->orWhere('target_audience', 'managers')
                        ->orWhere(function ($q) use ($branchId) {
                            $q->where('target_audience', 'branch')
                                ->where('branch_id', $branchId);
                        });
                });
            } else {
                $notices->whereRaw('1 = 0');
            }
        }

        $notices = $notices->latest()->get();

        return view('admin.notices.index', compact('notices'));
    }

    public function create()
    {
        abort_if(Gate::denies('notice_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branchId = $this->getUserBranchId();
        $ids = $this->getTeacherAssignmentIds();

        $branches = Branch::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $courses = Course::where('status', 'active')
            ->when(! auth()->user()->is_admin && ! $this->isTeacher(), function ($query) use ($branchId) {
                $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->when($this->isTeacher(), function ($query) use ($ids) {
                $ids['course_ids']->count()
                    ? $query->whereIn('id', $ids['course_ids'])
                    : $query->whereRaw('1 = 0');
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $batches = Batch::where('status', 'active')
            ->when(! auth()->user()->is_admin && ! $this->isTeacher(), function ($query) use ($branchId) {
                $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->when($this->isTeacher(), function ($query) use ($ids) {
                $ids['batch_ids']->count()
                    ? $query->whereIn('id', $ids['batch_ids'])
                    : $query->whereRaw('1 = 0');
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $users = User::pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $noticeTypes = $this->noticeTypes();
        $targetAudiences = $this->targetAudiences();
        $batchesByBranch = $this->batchesByBranch();
        $coursesByBatch = $this->coursesByBatch();

        return view('admin.notices.create', compact(
            'branches',
            'courses',
            'batches',
            'users',
            'noticeTypes',
            'targetAudiences',
            'batchesByBranch',
            'coursesByBatch'
        ));
    }

    public function store(StoreNoticeRequest $request)
    {
        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validated();

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            if (in_array($data['target_audience'] ?? null, ['branch', 'course', 'batch'])) {
                $data['branch_id'] = $branchId;
            }

            if ($this->isTeacher()) {
                $this->validateTeacherNoticeData($data);
            } else {
                $this->validateBranchNoticeData($data, $branchId);
            }
        }

        if (empty($data['created_by_id'])) {
            $data['created_by_id'] = auth()->id();
        }

        if (empty($data['publish_date'])) {
            $data['publish_date'] = now()->format('Y-m-d');
        }

        $notice = Notice::create($data);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $notice->addMedia($file)->toMediaCollection('notice_attachments');
            }
        }

        return redirect()->route('admin.notices.index')->with('message', 'Notice created successfully.');
    }

    public function show(Notice $notice)
    {
        abort_if(Gate::denies('notice_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkNoticeAccess($notice);

        $notice->load(['branch', 'course', 'batch', 'createdBy']);

        return view('admin.notices.show', compact('notice'));
    }

    public function edit(Notice $notice)
    {
        abort_if(Gate::denies('notice_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkNoticeAccess($notice);

        $branchId = $this->getUserBranchId();
        $ids = $this->getTeacherAssignmentIds();

        $branches = Branch::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $courses = Course::where('status', 'active')
            ->when(! auth()->user()->is_admin && ! $this->isTeacher(), function ($query) use ($branchId) {
                $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->when($this->isTeacher(), function ($query) use ($ids) {
                $ids['course_ids']->count()
                    ? $query->whereIn('id', $ids['course_ids'])
                    : $query->whereRaw('1 = 0');
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $batches = Batch::where('status', 'active')
            ->when(! auth()->user()->is_admin && ! $this->isTeacher(), function ($query) use ($branchId) {
                $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->when($this->isTeacher(), function ($query) use ($ids) {
                $ids['batch_ids']->count()
                    ? $query->whereIn('id', $ids['batch_ids'])
                    : $query->whereRaw('1 = 0');
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $users = User::pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $noticeTypes = $this->noticeTypes();
        $targetAudiences = $this->targetAudiences();
        $batchesByBranch = $this->batchesByBranch();
        $coursesByBatch = $this->coursesByBatch();

        $notice->load(['branch', 'course', 'batch', 'createdBy']);

        return view('admin.notices.edit', compact(
            'notice',
            'branches',
            'courses',
            'batches',
            'users',
            'noticeTypes',
            'targetAudiences',
            'batchesByBranch',
            'coursesByBatch'
        ));
    }

    public function update(UpdateNoticeRequest $request, Notice $notice)
    {
        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkNoticeAccess($notice);

        $data = $request->validated();

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            if (in_array($data['target_audience'] ?? null, ['branch', 'course', 'batch'])) {
                $data['branch_id'] = $branchId;
            }

            if ($this->isTeacher()) {
                $this->validateTeacherNoticeData($data);
            } else {
                $this->validateBranchNoticeData($data, $branchId);
            }
        }

        if (empty($data['created_by_id'])) {
            $data['created_by_id'] = $notice->created_by_id ?? auth()->id();
        }

        $notice->update($data);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $notice->addMedia($file)->toMediaCollection('notice_attachments');
            }
        }

        return redirect()->route('admin.notices.index')->with('message', 'Notice updated successfully.');
    }

    public function destroy(Notice $notice)
    {
        abort_if(Gate::denies('notice_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkNoticeAccess($notice);

        $notice->delete();

        return back()->with('message', 'Notice deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('notice_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Notice::whereIn('id', request('ids'));

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
        }

        $query->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function deleteMedia(Media $media)
    {
        abort_if(Gate::denies('notice_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $notice = $media->model;

        if ($notice instanceof Notice) {
            $this->checkNoticeAccess($notice);
        }

        $media->delete();

        return back()->with('message', 'Attachment deleted successfully.');
    }

    private function checkNoticeAccess(Notice $notice): void
    {
        if (auth()->user()->is_admin) {
            return;
        }

        if ($notice->target_audience === 'all') {
            return;
        }

        if ($this->isStudent()) {
            $student = Student::where('user_id', auth()->id())->first();

            $allowed = $student && (
                $notice->target_audience === 'students' ||
                ($notice->target_audience === 'branch' && $notice->branch_id == $student->branch_id) ||
                ($notice->target_audience === 'course' && $notice->course_id == $student->course_id) ||
                ($notice->target_audience === 'batch' && $notice->batch_id == $student->batch_id)
            );

            abort_if(! $allowed, Response::HTTP_FORBIDDEN, '403 Forbidden');

            return;
        }

        if ($this->isTeacher()) {
            $ids = $this->getTeacherAssignmentIds();
            $branchId = $this->getUserBranchId();

            $allowed =
                $notice->target_audience === 'teachers' ||
                ($notice->target_audience === 'branch' && $notice->branch_id == $branchId) ||
                ($notice->target_audience === 'course' && $ids['course_ids']->contains($notice->course_id)) ||
                ($notice->target_audience === 'batch' && $ids['batch_ids']->contains($notice->batch_id));

            abort_if(! $allowed, Response::HTTP_FORBIDDEN, '403 Forbidden');

            return;
        }

        if ($this->isStaff()) {
            $branchId = $this->getUserBranchId();

            $allowed =
                $notice->target_audience === 'staff' ||
                ($notice->target_audience === 'branch' && $notice->branch_id == $branchId);

            abort_if(! $allowed, Response::HTTP_FORBIDDEN, '403 Forbidden');

            return;
        }

        $branchId = $this->getUserBranchId();

        $allowed =
            $notice->target_audience === 'managers' ||
            ($notice->target_audience === 'branch' && $notice->branch_id == $branchId);

        abort_if(! $allowed, Response::HTTP_FORBIDDEN, '403 Forbidden');
    }

    private function validateBranchNoticeData(array $data, $branchId): void
    {
        if (! empty($data['branch_id'])) {
            abort_if($data['branch_id'] != $branchId, Response::HTTP_FORBIDDEN, 'Invalid branch.');
        }

        if (! empty($data['course_id'])) {
            abort_if(! Course::where('id', $data['course_id'])->where('branch_id', $branchId)->exists(), Response::HTTP_FORBIDDEN, 'Invalid course.');
        }

        if (! empty($data['batch_id'])) {
            abort_if(! Batch::where('id', $data['batch_id'])->where('branch_id', $branchId)->exists(), Response::HTTP_FORBIDDEN, 'Invalid batch.');
        }
    }

    private function validateTeacherNoticeData(array $data): void
    {
        $ids = $this->getTeacherAssignmentIds();

        if (! empty($data['course_id'])) {
            abort_if(! $ids['course_ids']->contains((int) $data['course_id']), Response::HTTP_FORBIDDEN, 'Invalid course.');
        }

        if (! empty($data['batch_id'])) {
            abort_if(! $ids['batch_ids']->contains((int) $data['batch_id']), Response::HTTP_FORBIDDEN, 'Invalid batch.');
        }
    }

    private function noticeTypes(): array
    {
        return [
            'General Notice'       => 'General Notice',
            'Holiday Notice'       => 'Holiday Notice',
            'Exam Notice'          => 'Exam Notice',
            'Fee Notice'           => 'Fee Notice',
            'Admission Notice'     => 'Admission Notice',
            'Class Timing Notice'  => 'Class Timing Notice',
            'Event Notice'         => 'Event Notice',
            'Urgent Notice'        => 'Urgent Notice',
            'Other'                => 'Other',
        ];
    }

    private function targetAudiences(): array
    {
        return [
            'all'      => 'All',
            'students' => 'Students',
            'teachers' => 'Teachers',
            'staff'    => 'Staff',
            'managers' => 'Managers',
            'branch'   => 'Specific Branch',
            'course'   => 'Specific Course',
            'batch'    => 'Specific Batch',
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
                'course_ids' => collect([]),
                'batch_ids'  => collect([]),
            ];
        }

        $assignments = TeacherAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->get();

        return [
            'course_ids' => $assignments->pluck('course_id')->filter()->unique()->values(),
            'batch_ids'  => $assignments->pluck('batch_id')->filter()->unique()->values(),
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

    private function isStaff(): bool
    {
        return auth()->user()->roles()->where('title', 'Staff')->exists();
    }
}