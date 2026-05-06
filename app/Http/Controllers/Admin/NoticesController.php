<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNoticeRequest;
use App\Http\Requests\UpdateNoticeRequest;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Notice;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class NoticesController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('notice_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $notices = Notice::with(['branch', 'course', 'batch', 'createdBy'])
            ->latest()
            ->get();

        return view('admin.notices.index', compact('notices'));
    }

    public function create()
    {
        abort_if(Gate::denies('notice_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branches = Branch::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $courses = Course::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $batches = Batch::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $users = User::pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $noticeTypes = $this->noticeTypes();
        $targetAudiences = $this->targetAudiences();

        return view('admin.notices.create', compact(
            'branches',
            'courses',
            'batches',
            'users',
            'noticeTypes',
            'targetAudiences'
        ));
    }

    public function store(StoreNoticeRequest $request)
    {
        $data = $request->validated();

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

        $notice->load(['branch', 'course', 'batch', 'createdBy']);

        return view('admin.notices.show', compact('notice'));
    }

    public function edit(Notice $notice)
    {
        abort_if(Gate::denies('notice_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branches = Branch::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $courses = Course::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $batches = Batch::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $users = User::pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $noticeTypes = $this->noticeTypes();
        $targetAudiences = $this->targetAudiences();

        $notice->load(['branch', 'course', 'batch', 'createdBy']);

        return view('admin.notices.edit', compact(
            'notice',
            'branches',
            'courses',
            'batches',
            'users',
            'noticeTypes',
            'targetAudiences'
        ));
    }

    public function update(UpdateNoticeRequest $request, Notice $notice)
    {
        $data = $request->validated();

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

        $notice->delete();

        return back()->with('message', 'Notice deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('notice_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Notice::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function deleteMedia(Media $media)
    {
        abort_if(Gate::denies('notice_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $media->delete();

        return back()->with('message', 'Attachment deleted successfully.');
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
}