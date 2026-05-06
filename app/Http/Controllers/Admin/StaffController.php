<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Models\Branch;
use App\Models\Staff;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('staff_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $staff = Staff::with(['user', 'branch'])->latest()->get();

        return view('admin.staff.index', compact('staff'));
    }

    public function create()
    {
        abort_if(Gate::denies('staff_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $users = User::whereHas('roles', function ($query) {
                $query->where('title', 'Staff');
            })
            ->whereDoesntHave('staff')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $branches = Branch::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        return view('admin.staff.create', compact('users', 'branches'));
    }

    public function store(StoreStaffRequest $request)
    {
        $staff = Staff::create($request->validated());

        if ($request->hasFile('photo')) {
            $staff->addMediaFromRequest('photo')->toMediaCollection('staff_photo');
        }

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $staff->addMedia($document)->toMediaCollection('staff_documents');
            }
        }

        return redirect()->route('admin.staff.index')->with('message', 'Staff created successfully.');
    }

    public function show(Staff $staff)
    {
        abort_if(Gate::denies('staff_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $staff->load(['user', 'branch']);

        return view('admin.staff.show', compact('staff'));
    }

    public function edit(Staff $staff)
    {
        abort_if(Gate::denies('staff_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $users = User::whereHas('roles', function ($query) {
                $query->where('title', 'Staff');
            })
            ->where(function ($query) use ($staff) {
                $query->whereDoesntHave('staff')
                    ->orWhere('id', $staff->user_id);
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $branches = Branch::where('status', 'active')
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $staff->load(['user', 'branch']);

        return view('admin.staff.edit', compact('staff', 'users', 'branches'));
    }

    public function update(UpdateStaffRequest $request, Staff $staff)
    {
        $staff->update($request->validated());

        if ($request->hasFile('photo')) {
            $staff->clearMediaCollection('staff_photo');
            $staff->addMediaFromRequest('photo')->toMediaCollection('staff_photo');
        }

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $staff->addMedia($document)->toMediaCollection('staff_documents');
            }
        }

        return redirect()->route('admin.staff.index')->with('message', 'Staff updated successfully.');
    }

    public function destroy(Staff $staff)
    {
        abort_if(Gate::denies('staff_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $staff->delete();

        return back()->with('message', 'Staff deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('staff_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        Staff::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}