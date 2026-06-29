<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\SyncsProfileUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Models\Branch;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffController extends Controller
{
    use SyncsProfileUser;

    public function index()
    {
        abort_if(Gate::denies('staff_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branchId = $this->getUserBranchId();

        $staff = Staff::with(['user', 'branch']);

        if (auth()->user()->is_admin) {
            // Admin ko all staff
        } elseif ($this->isStaff()) {
            // Staff login ko sirf apna profile
            $authStaff = Staff::where('user_id', auth()->id())->first();

            if ($authStaff) {
                $staff->where('id', $authStaff->id);
            } else {
                $staff->whereRaw('1 = 0');
            }
        } else {
            // Branch Manager ko apni branch ke staff
            if ($branchId) {
                $staff->where('branch_id', $branchId);
            } else {
                $staff->whereRaw('1 = 0');
            }
        }

        $staff = $staff->latest()->get();

        return view('admin.staff.index', compact('staff'));
    }

    public function create()
    {
        abort_if(Gate::denies('staff_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Staff/Teacher/Student ko create allow nahi
        abort_if($this->isStaff() || $this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branchId = $this->getUserBranchId();

        ['users' => $users, 'userDetails' => $userDetails] = $this->profileUserSelectData('Staff', 'staffProfile');

        $branches = Branch::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where('id', $branchId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        return view('admin.staff.create', compact('users', 'userDetails', 'branches'));
    }

    public function store(StoreStaffRequest $request)
    {
        abort_if($this->isStaff() || $this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validated();

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;
        }

        $staff = DB::transaction(function () use ($data) {
            $user = $this->syncProfileUser($data, 'Staff');
            $data['user_id'] = $user->id;

            return Staff::create($this->profileData($data));
        });

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

        $this->checkStaffAccess($staff);

        $staff->load(['user', 'branch']);

        return view('admin.staff.show', compact('staff'));
    }

    public function edit(Staff $staff)
    {
        abort_if(Gate::denies('staff_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Staff/Teacher/Student ko edit allow nahi
        abort_if($this->isStaff() || $this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkStaffAccess($staff);

        $branchId = $this->getUserBranchId();

        ['users' => $users, 'userDetails' => $userDetails] = $this->profileUserSelectData('Staff', 'staffProfile', $staff->user_id);

        $branches = Branch::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where('id', $branchId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $staff->load(['user', 'branch']);

        return view('admin.staff.edit', compact('staff', 'users', 'userDetails', 'branches'));
    }

    public function update(UpdateStaffRequest $request, Staff $staff)
    {
        abort_if($this->isStaff() || $this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkStaffAccess($staff);

        $data = $request->validated();

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;
        }

        DB::transaction(function () use ($staff, $data) {
            $data['user_id'] = $data['user_id'] ?? $staff->user_id;

            $user = $this->syncProfileUser($data, 'Staff');
            $data['user_id'] = $user->id;

            $staff->update($this->profileData($data));
        });

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

        abort_if($this->isStaff() || $this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkStaffAccess($staff);

        $staff->delete();

        return back()->with('message', 'Staff deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('staff_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStaff() || $this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branchId = $this->getUserBranchId();

        $query = Staff::whereIn('id', request('ids'));

        if (! auth()->user()->is_admin) {
            if ($branchId) {
                $query->where('branch_id', $branchId);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $query->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function checkStaffAccess(Staff $staff): void
    {
        if (auth()->user()->is_admin) {
            return;
        }

        if ($this->isStaff()) {
            $authStaff = Staff::where('user_id', auth()->id())->first();

            abort_if(! $authStaff || $staff->id != $authStaff->id, Response::HTTP_FORBIDDEN, '403 Forbidden');

            return;
        }

        $branchId = $this->getUserBranchId();

        abort_if(! $branchId || $staff->branch_id != $branchId, Response::HTTP_FORBIDDEN, '403 Forbidden');
    }

    private function getUserBranchId()
    {
        $user = auth()->user();

        if ($user->is_admin) {
            return null;
        }

        // Branch Manager: branches.manager_id = users.id
        $managedBranch = Branch::where('manager_id', $user->id)->first();

        if ($managedBranch) {
            return $managedBranch->id;
        }

        // Staff
        $staff = Staff::where('user_id', $user->id)->first();

        if ($staff) {
            return $staff->branch_id;
        }

        // Teacher
        $teacher = Teacher::where('user_id', $user->id)->first();

        if ($teacher) {
            return $teacher->branch_id;
        }

        // Student
        $student = Student::where('user_id', $user->id)->first();

        if ($student) {
            return $student->branch_id;
        }

        return null;
    }

    private function isStaff(): bool
    {
        return auth()->user()
            ->roles()
            ->where('title', 'Staff')
            ->exists();
    }

    private function isTeacher(): bool
    {
        return auth()->user()
            ->roles()
            ->where('title', 'Teacher')
            ->exists();
    }

    private function isStudent(): bool
    {
        return auth()->user()
            ->roles()
            ->where('title', 'Student')
            ->exists();
    }
}
