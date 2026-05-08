<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEnquiryFollowUpRequest;
use App\Http\Requests\StoreEnquiryRequest;
use App\Http\Requests\UpdateEnquiryRequest;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Enquiry;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnquiriesController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('enquiry_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $enquiries = Enquiry::with(['branch', 'course', 'assignedTo']);

        if (auth()->user()->is_admin) {
            // Admin ko all enquiries
        } elseif ($this->isStaff()) {
            $branchId = $this->getUserBranchId();

            if ($branchId) {
                $enquiries->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhere('assigned_to_id', auth()->id());
                });
            } else {
                $enquiries->where('assigned_to_id', auth()->id());
            }
        } else {
            $branchId = $this->getUserBranchId();

            if ($branchId) {
                $enquiries->where('branch_id', $branchId);
            } else {
                $enquiries->whereRaw('1 = 0');
            }
        }

        $enquiries = $enquiries->latest()->get();

        return view('admin.enquiries.index', compact('enquiries'));
    }

    public function create()
    {
        abort_if(Gate::denies('enquiry_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branchId = $this->getUserBranchId();

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

        $courses = Course::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $users = User::whereHas('roles', function ($query) {
                $query->whereIn('title', ['Admin', 'Branch Manager', 'Staff']);
            })
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where(function ($q) use ($branchId) {
                        $q->whereHas('staffProfile', function ($staffQuery) use ($branchId) {
                            $staffQuery->where('branch_id', $branchId);
                        })
                        ->orWhereHas('managedBranch', function ($branchQuery) use ($branchId) {
                            $branchQuery->where('id', $branchId);
                        })
                        ->orWhere('id', auth()->id());
                    });
                } else {
                    $query->where('id', auth()->id());
                }
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $sources = $this->sources();

        return view('admin.enquiries.create', compact('branches', 'courses', 'users', 'sources'));
    }

    public function store(StoreEnquiryRequest $request)
    {
        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validated();

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;

            if (! empty($data['course_id'])) {
                $course = Course::where('id', $data['course_id'])
                    ->where('branch_id', $branchId)
                    ->first();

                abort_if(! $course, Response::HTTP_FORBIDDEN, 'Invalid course for your branch.');
            }
        }

        Enquiry::create($data);

        return redirect()->route('admin.enquiries.index')->with('message', 'Enquiry created successfully.');
    }

    public function show(Enquiry $enquiry)
    {
        abort_if(Gate::denies('enquiry_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkEnquiryAccess($enquiry);

        $enquiry->load(['branch', 'course', 'assignedTo', 'followUps.followedBy']);

        $followUpTypes = [
            'Call' => 'Call',
            'WhatsApp' => 'WhatsApp',
            'SMS' => 'SMS',
            'Email' => 'Email',
            'Visit' => 'Visit',
            'Demo Class' => 'Demo Class',
            'Other' => 'Other',
        ];

        return view('admin.enquiries.show', compact('enquiry', 'followUpTypes'));
    }

    public function edit(Enquiry $enquiry)
    {
        abort_if(Gate::denies('enquiry_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkEnquiryAccess($enquiry);

        $branchId = $this->getUserBranchId();

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

        $courses = Course::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $users = User::whereHas('roles', function ($query) {
                $query->whereIn('title', ['Admin', 'Branch Manager', 'Staff']);
            })
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where(function ($q) use ($branchId) {
                        $q->whereHas('staffProfile', function ($staffQuery) use ($branchId) {
                            $staffQuery->where('branch_id', $branchId);
                        })
                        ->orWhereHas('managedBranch', function ($branchQuery) use ($branchId) {
                            $branchQuery->where('id', $branchId);
                        })
                        ->orWhere('id', auth()->id());
                    });
                } else {
                    $query->where('id', auth()->id());
                }
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $sources = $this->sources();

        $enquiry->load(['branch', 'course', 'assignedTo']);

        return view('admin.enquiries.edit', compact('enquiry', 'branches', 'courses', 'users', 'sources'));
    }

    public function update(UpdateEnquiryRequest $request, Enquiry $enquiry)
    {
        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkEnquiryAccess($enquiry);

        $data = $request->validated();

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;

            if (! empty($data['course_id'])) {
                $course = Course::where('id', $data['course_id'])
                    ->where('branch_id', $branchId)
                    ->first();

                abort_if(! $course, Response::HTTP_FORBIDDEN, 'Invalid course for your branch.');
            }
        }

        $enquiry->update($data);

        return redirect()->route('admin.enquiries.index')->with('message', 'Enquiry updated successfully.');
    }

    public function destroy(Enquiry $enquiry)
    {
        abort_if(Gate::denies('enquiry_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkEnquiryAccess($enquiry);

        $enquiry->delete();

        return back()->with('message', 'Enquiry deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('enquiry_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Enquiry::whereIn('id', request('ids'));

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            if ($branchId) {
                $query->where('branch_id', $branchId);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $query->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function storeFollowUp(StoreEnquiryFollowUpRequest $request, Enquiry $enquiry)
    {
        abort_if(Gate::denies('enquiry_follow_up_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkEnquiryAccess($enquiry);

        $enquiry->followUps()->create([
            'followed_by_id'       => auth()->id(),
            'follow_up_date'       => $request->follow_up_date,
            'follow_up_type'       => $request->follow_up_type,
            'response'             => $request->response,
            'next_follow_up_date'  => $request->next_follow_up_date,
            'status'               => $request->status,
            'remarks'              => $request->remarks,
        ]);

        $enquiry->update([
            'status' => $request->status,
            'next_follow_up_date' => $request->next_follow_up_date,
        ]);

        return back()->with('message', 'Follow-up added successfully.');
    }

    private function checkEnquiryAccess(Enquiry $enquiry): void
    {
        if (auth()->user()->is_admin) {
            return;
        }

        if ($this->isStaff() && $enquiry->assigned_to_id == auth()->id()) {
            return;
        }

        $branchId = $this->getUserBranchId();

        abort_if(! $branchId || $enquiry->branch_id != $branchId, Response::HTTP_FORBIDDEN, '403 Forbidden');
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

    private function sources(): array
    {
        return [
            'Walk-in' => 'Walk-in',
            'Phone Call' => 'Phone Call',
            'Website' => 'Website',
            'Facebook' => 'Facebook',
            'Instagram' => 'Instagram',
            'Google' => 'Google',
            'Reference' => 'Reference',
            'WhatsApp' => 'WhatsApp',
            'Other' => 'Other',
        ];
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