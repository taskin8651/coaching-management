<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEnquiryFollowUpRequest;
use App\Http\Requests\StoreEnquiryRequest;
use App\Http\Requests\UpdateEnquiryRequest;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Enquiry;
use App\Models\Role;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnquiriesController extends Controller
{
    use AppliesErpScope;
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
        $coursesByBranch = $this->coursesByBranch();

        return view('admin.enquiries.create', compact('branches', 'courses', 'users', 'sources', 'coursesByBranch'));
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
        $coursesByBranch = $this->coursesByBranch();

        $enquiry->load(['branch', 'course', 'assignedTo']);

        return view('admin.enquiries.edit', compact('enquiry', 'branches', 'courses', 'users', 'sources', 'coursesByBranch'));
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

        // Staff can also convert an enquiry by simply picking "Converted" from the Status
        // dropdown here — that must create the same student account as the dedicated
        // "Convert to Student" button, not just flip the status text. Only short-circuit the
        // status write when we're actually allowed (and about) to run that conversion, so a
        // user without student_create still gets their plain status change saved as-is.
        $justConverted = ($data['status'] ?? null) === 'converted'
            && $enquiry->status !== 'converted'
            && Gate::allows('student_create');

        if ($justConverted) {
            unset($data['status']);
        }

        $enquiry->update($data);

        if ($justConverted) {
            $result = $this->createStudentFromEnquiry($enquiry);

            return redirect()
                ->route('admin.students.show', $result['student']->id)
                ->with('message', sprintf(
                    'Enquiry updated. Student account created. Login Email: %s, Password: %s.',
                    $result['email'],
                    $result['password']
                ));
        }

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

    /**
     * One-click Enquiry -> Student conversion: creates the login User + Student profile
     * straight from the enquiry's captured data (no re-typing) and hands off to the new
     * student's profile.
     */
    public function convert(Enquiry $enquiry)
    {
        abort_if(Gate::denies('student_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isTeacher() || $this->isStudent(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkEnquiryAccess($enquiry);

        abort_if($enquiry->status === 'converted', Response::HTTP_UNPROCESSABLE_ENTITY, 'Enquiry is already converted.');

        $result = $this->createStudentFromEnquiry($enquiry);

        return redirect()
            ->route('admin.students.show', $result['student']->id)
            ->with('message', sprintf(
                'Student account created. Login Email: %s, Password: %s.',
                $result['email'],
                $result['password']
            ));
    }

    /**
     * Creates the login User + Student profile from an enquiry's captured data and marks the
     * enquiry converted. Shared by the explicit "Convert to Student" action and by update()
     * when staff instead just flips the Status dropdown to Converted on the Edit form — both
     * paths must produce the same account. Returns the new student plus the login email and
     * default password so the caller can show them to staff (no WhatsApp send here).
     */
    private function createStudentFromEnquiry(Enquiry $enquiry): array
    {
        $defaultPassword = 'Student@123';

        $user = User::create([
            'name'      => $enquiry->student_name,
            'email'     => $this->resolveUserEmail($enquiry),
            'password'  => $defaultPassword,
            'phone'     => $enquiry->phone,
            'branch_id' => $enquiry->branch_id,
        ]);

        $roleId = Role::where('title', 'Student')->value('id');

        if ($roleId) {
            $user->roles()->syncWithoutDetaching([$roleId]);
        }

        $student = Student::create([
            'user_id'         => $user->id,
            'branch_id'       => $enquiry->branch_id,
            'course_id'       => $enquiry->course_id,
            'phone'           => $enquiry->phone,
            'alternate_phone' => $enquiry->alternate_phone,
            'school_name'     => $enquiry->school_name,
            'class_name'      => $enquiry->class_name,
            'status'          => 'active',
        ]);

        $enquiry->update(['status' => 'converted']);

        return [
            'student'  => $student,
            'email'    => $user->email,
            'password' => $defaultPassword,
        ];
    }

    /**
     * A real, unique email if the enquiry has one and it's free; otherwise a synthetic
     * `<name>.<phone>@students.local` address, since Users require a unique, non-null email.
     */
    private function resolveUserEmail(Enquiry $enquiry): string
    {
        if ($enquiry->email && ! User::where('email', $enquiry->email)->exists()) {
            return $enquiry->email;
        }

        $base = Str::slug($enquiry->student_name ?: 'student') ?: 'student';
        $phoneDigits = preg_replace('/\D/', '', (string) $enquiry->phone);
        $identifier = $phoneDigits ?: $enquiry->id;

        $candidate = "{$base}.{$identifier}@students.local";
        $suffix = 1;

        while (User::where('email', $candidate)->exists()) {
            $candidate = "{$base}.{$identifier}.{$suffix}@students.local";
            $suffix++;
        }

        return $candidate;
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