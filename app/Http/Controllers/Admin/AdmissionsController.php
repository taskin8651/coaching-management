<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdmissionRequest;
use App\Http\Requests\UpdateAdmissionRequest;
use App\Models\Admission;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Enquiry;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Gate;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class AdmissionsController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('admission_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $admissions = Admission::with([
            'student.user',
            'branch',
            'course',
            'batch',
            'enquiry',
            'createdBy',
        ]);

        if (auth()->user()->is_admin) {
            // Admin all
        } elseif ($this->isStudent()) {
            $student = Student::where('user_id', auth()->id())->first();

            $student
                ? $admissions->where('student_id', $student->id)
                : $admissions->whereRaw('1 = 0');
        } elseif ($this->isTeacher()) {
            $batchIds = $this->getTeacherBatchIds();

            $batchIds->count()
                ? $admissions->whereIn('batch_id', $batchIds)
                : $admissions->whereRaw('1 = 0');
        } else {
            $branchId = $this->getUserBranchId();

            $branchId
                ? $admissions->where('branch_id', $branchId)
                : $admissions->whereRaw('1 = 0');
        }

        $admissions = $admissions->latest()->get();

        return view('admin.admissions.index', compact('admissions'));
    }

    public function create()
    {
        abort_if(Gate::denies('admission_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branchId = $this->getUserBranchId();

        $branches = Branch::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $students = Student::with('user')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->get()
            ->pluck('user.name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $courses = Course::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $batches = Batch::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $enquiries = Enquiry::query()
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->latest()
            ->get()
            ->mapWithKeys(function ($enquiry) {
                $name = $enquiry->student_name ?? $enquiry->name ?? 'Enquiry';
                $phone = $enquiry->phone ?? $enquiry->mobile ?? '';

                return [$enquiry->id => trim($name . ' - ' . $phone)];
            })
            ->prepend(trans('global.pleaseSelect'), '');

        $statuses = $this->statuses();
        $sources = $this->sources();

        return view('admin.admissions.create', compact(
            'branches',
            'students',
            'courses',
            'batches',
            'enquiries',
            'statuses',
            'sources'
        ));
    }

    public function store(StoreAdmissionRequest $request)
    {
        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $this->prepareAdmissionData($request->validated());

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;

            $this->validateBranchData($data, $branchId);
        }

        $admission = Admission::create($data);

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $admission->addMedia($document)->toMediaCollection('admission_documents');
            }
        }

        if (! empty($data['enquiry_id']) && class_exists(Enquiry::class)) {
            $enquiry = Enquiry::find($data['enquiry_id']);

            if ($enquiry) {
                $enquiry->update([
                    'status' => 'converted',
                ]);
            }
        }

        return redirect()->route('admin.admissions.index')->with('message', 'Admission created successfully.');
    }

    public function show(Admission $admission)
    {
        abort_if(Gate::denies('admission_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAdmissionAccess($admission);

        $admission->load([
            'student.user',
            'branch',
            'course',
            'batch',
            'enquiry',
            'createdBy',
        ]);

        return view('admin.admissions.show', compact('admission'));
    }

    public function edit(Admission $admission)
    {
        abort_if(Gate::denies('admission_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAdmissionAccess($admission);

        $branchId = $this->getUserBranchId();

        $branches = Branch::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $students = Student::with('user')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->get()
            ->pluck('user.name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $courses = Course::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $batches = Batch::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $enquiries = Enquiry::query()
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
            })
            ->latest()
            ->get()
            ->mapWithKeys(function ($enquiry) {
                $name = $enquiry->student_name ?? $enquiry->name ?? 'Enquiry';
                $phone = $enquiry->phone ?? $enquiry->mobile ?? '';

                return [$enquiry->id => trim($name . ' - ' . $phone)];
            })
            ->prepend(trans('global.pleaseSelect'), '');

        $statuses = $this->statuses();
        $sources = $this->sources();

        $admission->load([
            'student.user',
            'branch',
            'course',
            'batch',
            'enquiry',
            'createdBy',
        ]);

        return view('admin.admissions.edit', compact(
            'admission',
            'branches',
            'students',
            'courses',
            'batches',
            'enquiries',
            'statuses',
            'sources'
        ));
    }

    public function update(UpdateAdmissionRequest $request, Admission $admission)
    {
        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAdmissionAccess($admission);

        $data = $this->prepareAdmissionData($request->validated(), $admission);

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            abort_if(! $branchId, Response::HTTP_FORBIDDEN, 'Branch not assigned.');

            $data['branch_id'] = $branchId;

            $this->validateBranchData($data, $branchId);
        }

        $admission->update($data);

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $admission->addMedia($document)->toMediaCollection('admission_documents');
            }
        }

        return redirect()->route('admin.admissions.index')->with('message', 'Admission updated successfully.');
    }

    public function destroy(Admission $admission)
    {
        abort_if(Gate::denies('admission_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAdmissionAccess($admission);

        $admission->delete();

        return back()->with('message', 'Admission deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('admission_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Admission::whereIn('id', request('ids'));

        if (! auth()->user()->is_admin) {
            $branchId = $this->getUserBranchId();

            $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1 = 0');
        }

        $query->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function deleteMedia(Media $media)
    {
        abort_if(Gate::denies('admission_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $admission = $media->model;

        if ($admission instanceof Admission) {
            $this->checkAdmissionAccess($admission);
        }

        $media->delete();

        return back()->with('message', 'Document deleted successfully.');
    }

    private function prepareAdmissionData(array $data, Admission $admission = null): array
    {
        $courseFee = (float) ($data['course_fee'] ?? 0);
        $admissionFee = (float) ($data['admission_fee'] ?? 0);
        $discount = (float) ($data['discount'] ?? 0);

        $data['course_fee'] = $courseFee;
        $data['admission_fee'] = $admissionFee;
        $data['discount'] = $discount;
        $data['payable_amount'] = max(($courseFee + $admissionFee) - $discount, 0);

        if (empty($data['admission_no'])) {
            $data['admission_no'] = $admission->admission_no ?? $this->generateAdmissionNo();
        }

        if (empty($data['admission_date'])) {
            $data['admission_date'] = now()->format('Y-m-d');
        }

        if (empty($data['created_by_id'])) {
            $data['created_by_id'] = $admission->created_by_id ?? auth()->id();
        }

        if (! empty($data['student_id'])) {
            $student = Student::find($data['student_id']);

            if ($student) {
                $data['branch_id'] = $data['branch_id'] ?? $student->branch_id;
                $data['course_id'] = $data['course_id'] ?? $student->course_id;
                $data['batch_id'] = $data['batch_id'] ?? $student->batch_id;
            }
        }

        return $data;
    }

    private function generateAdmissionNo(): string
    {
        $lastAdmission = Admission::latest('id')->first();
        $nextId = $lastAdmission ? $lastAdmission->id + 1 : 1;

        return 'ADM-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }

    private function checkAdmissionAccess(Admission $admission): void
    {
        if (auth()->user()->is_admin) {
            return;
        }

        if ($this->isStudent()) {
            $student = Student::where('user_id', auth()->id())->first();

            abort_if(! $student || $admission->student_id != $student->id, Response::HTTP_FORBIDDEN, '403 Forbidden');

            return;
        }

        if ($this->isTeacher()) {
            $batchIds = $this->getTeacherBatchIds();

            abort_if(! $batchIds->contains($admission->batch_id), Response::HTTP_FORBIDDEN, '403 Forbidden');

            return;
        }

        $branchId = $this->getUserBranchId();

        abort_if(! $branchId || $admission->branch_id != $branchId, Response::HTTP_FORBIDDEN, '403 Forbidden');
    }

    private function validateBranchData(array $data, $branchId): void
    {
        if (! empty($data['student_id'])) {
            abort_if(! Student::where('id', $data['student_id'])->where('branch_id', $branchId)->exists(), Response::HTTP_FORBIDDEN, 'Invalid student for your branch.');
        }

        if (! empty($data['course_id'])) {
            abort_if(! Course::where('id', $data['course_id'])->where('branch_id', $branchId)->exists(), Response::HTTP_FORBIDDEN, 'Invalid course for your branch.');
        }

        if (! empty($data['batch_id'])) {
            abort_if(! Batch::where('id', $data['batch_id'])->where('branch_id', $branchId)->exists(), Response::HTTP_FORBIDDEN, 'Invalid batch for your branch.');
        }

        if (! empty($data['enquiry_id'])) {
            abort_if(! Enquiry::where('id', $data['enquiry_id'])->where('branch_id', $branchId)->exists(), Response::HTTP_FORBIDDEN, 'Invalid enquiry for your branch.');
        }
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

    private function getTeacherBatchIds()
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();

        if (! $teacher) {
            return collect([]);
        }

        return TeacherAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->whereNotNull('batch_id')
            ->pluck('batch_id')
            ->unique()
            ->values();
    }

    private function statuses(): array
    {
        return [
            'pending'   => 'Pending',
            'confirmed' => 'Confirmed',
            'rejected'  => 'Rejected',
            'cancelled' => 'Cancelled',
            'completed' => 'Completed',
        ];
    }

    private function sources(): array
    {
        return [
            'Direct' => 'Direct',
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

    private function isTeacher(): bool
    {
        return auth()->user()->roles()->where('title', 'Teacher')->exists();
    }

    private function isStudent(): bool
    {
        return auth()->user()->roles()->where('title', 'Student')->exists();
    }
}