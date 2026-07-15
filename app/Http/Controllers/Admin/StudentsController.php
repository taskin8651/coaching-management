<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\SyncsProfileUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentBatch;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;
use Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StudentsController extends Controller
{
    use SyncsProfileUser;

    public function index()
    {
        abort_if(Gate::denies('student_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $students = Student::with(['user', 'branch', 'course', 'batch']);

        if (auth()->user()->is_admin) {
            // Admin ko all students
        } elseif ($this->isStudent()) {
            $authStudent = Student::where('user_id', auth()->id())->first();

            if ($authStudent) {
                $students->where('id', $authStudent->id);
            } else {
                $students->whereRaw('1 = 0');
            }
        } elseif ($this->isTeacher()) {
            $batchIds = $this->getTeacherBatchIds();

            if ($batchIds->count()) {
                $students->whereIn('batch_id', $batchIds);
            } else {
                $students->whereRaw('1 = 0');
            }
        } else {
            $branchId = $this->getUserBranchId();

            if ($branchId) {
                $students->where('branch_id', $branchId);
            } else {
                $students->whereRaw('1 = 0');
            }
        }

        $students = $students->latest()->get();

        return view('admin.students.index', compact('students'));
    }

    public function create()
    {
        abort_if(Gate::denies('student_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Student/Teacher ko student create allow nahi
        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branchId = $this->getUserBranchId();

        ['users' => $users, 'userDetails' => $userDetails] = $this->profileUserSelectData('Student', 'studentProfile');

        $guardians = User::whereHas('roles', function ($query) {
                $query->where('title', 'Parent');
            })
            ->pluck('name', 'id')
            ->prepend('Optional', '');

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

        $batches = Batch::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        return view('admin.students.create', compact('users', 'userDetails', 'guardians', 'branches', 'courses', 'batches'));
    }

    public function store(StoreStudentRequest $request)
    {
        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validated();
        $data['phone'] = $this->notificationPhone($data);

        $batchIds = array_values(array_unique(array_filter($data['batch_ids'] ?? [])));
        unset($data['batch_ids']);

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

            if (! empty($batchIds)) {
                $validBatchCount = Batch::whereIn('id', $batchIds)
                    ->where('branch_id', $branchId)
                    ->count();

                abort_if($validBatchCount !== count($batchIds), Response::HTTP_FORBIDDEN, 'Invalid batch for your branch.');
            }
        }

        $data['batch_id'] = $batchIds[0] ?? null;

        $student = DB::transaction(function () use ($data, $batchIds) {
            $user = $this->syncProfileUser($data, 'Student');
            $data['user_id'] = $user->id;

            $student = Student::create($this->profileData($data));

            $this->syncStudentBatches($student, $batchIds);

            return $student;
        });

        if ($request->hasFile('photo')) {
            $student->addMediaFromRequest('photo')->toMediaCollection('student_photo');
        }

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $student->addMedia($document)->toMediaCollection('student_documents');
            }
        }

        return redirect()->route('admin.students.index')->with('message', 'Student created successfully.');
    }

    public function show(Student $student)
    {
        abort_if(Gate::denies('student_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkStudentAccess($student);

        $student->load(['user', 'branch', 'course', 'batch']);

        return view('admin.students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        abort_if(Gate::denies('student_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Student/Teacher ko edit allow nahi
        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkStudentAccess($student);

        $branchId = $this->getUserBranchId();

        ['users' => $users, 'userDetails' => $userDetails] = $this->profileUserSelectData('Student', 'studentProfile', $student->user_id);

        $guardians = User::whereHas('roles', function ($query) {
                $query->where('title', 'Parent');
            })
            ->pluck('name', 'id')
            ->prepend('Optional', '');

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

        $batches = Batch::where('status', 'active')
            ->when(! auth()->user()->is_admin, function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->pluck('name', 'id')
            ->prepend(trans('global.pleaseSelect'), '');

        $student->load(['user', 'branch', 'course', 'batch', 'batches']);

        $selectedBatchIds = $student->batches->pluck('id')->toArray();

        if (empty($selectedBatchIds) && $student->batch_id) {
            $selectedBatchIds = [$student->batch_id];
        }

        return view('admin.students.edit', compact('student', 'users', 'userDetails', 'guardians', 'branches', 'courses', 'batches', 'selectedBatchIds'));
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkStudentAccess($student);

        $data = $request->validated();
        $data['phone'] = $this->notificationPhone($data);

        $batchIds = array_values(array_unique(array_filter($data['batch_ids'] ?? [])));
        unset($data['batch_ids']);

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

            if (! empty($batchIds)) {
                $validBatchCount = Batch::whereIn('id', $batchIds)
                    ->where('branch_id', $branchId)
                    ->count();

                abort_if($validBatchCount !== count($batchIds), Response::HTTP_FORBIDDEN, 'Invalid batch for your branch.');
            }
        }

        $data['batch_id'] = $batchIds[0] ?? null;

        DB::transaction(function () use ($student, $data, $batchIds) {
            $data['user_id'] = $data['user_id'] ?? $student->user_id;

            $user = $this->syncProfileUser($data, 'Student');
            $data['user_id'] = $user->id;

            $student->update($this->profileData($data));

            $this->syncStudentBatches($student, $batchIds);
        });

        if ($request->hasFile('photo')) {
            $student->clearMediaCollection('student_photo');
            $student->addMediaFromRequest('photo')->toMediaCollection('student_photo');
        }

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $student->addMedia($document)->toMediaCollection('student_documents');
            }
        }

        return redirect()->route('admin.students.index')->with('message', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        abort_if(Gate::denies('student_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkStudentAccess($student);

        $student->delete();

        return back()->with('message', 'Student deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('student_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        abort_if($this->isStudent() || $this->isTeacher(), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Student::whereIn('id', request('ids'));

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

    private function checkStudentAccess(Student $student): void
    {
        if (auth()->user()->is_admin) {
            return;
        }

        if ($this->isStudent()) {
            $authStudent = Student::where('user_id', auth()->id())->first();

            abort_if(! $authStudent || $student->id != $authStudent->id, Response::HTTP_FORBIDDEN, '403 Forbidden');

            return;
        }

        if ($this->isTeacher()) {
            $batchIds = $this->getTeacherBatchIds();

            abort_if(! $batchIds->contains($student->batch_id), Response::HTTP_FORBIDDEN, '403 Forbidden');

            return;
        }

        $branchId = $this->getUserBranchId();

        abort_if(! $branchId || $student->branch_id != $branchId, Response::HTTP_FORBIDDEN, '403 Forbidden');
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

    private function syncStudentBatches(Student $student, array $batchIds): void
    {
        $batchIds = array_map('intval', $batchIds);

        $existingBatchIds = StudentBatch::where('student_id', $student->id)
            ->whereNull('subject_id')
            ->pluck('batch_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $toDelete = array_diff($existingBatchIds, $batchIds);
        $toCreate = array_diff($batchIds, $existingBatchIds);

        if (! empty($toDelete)) {
            StudentBatch::where('student_id', $student->id)
                ->whereNull('subject_id')
                ->whereIn('batch_id', $toDelete)
                ->delete();
        }

        foreach ($toCreate as $batchId) {
            StudentBatch::create([
                'student_id' => $student->id,
                'batch_id' => $batchId,
                'status' => 'active',
            ]);
        }
    }

    private function isStudent(): bool
    {
        return auth()->user()
            ->roles()
            ->where('title', 'Student')
            ->exists();
    }

    private function isTeacher(): bool
    {
        return auth()->user()
            ->roles()
            ->where('title', 'Teacher')
            ->exists();
    }

    private function notificationPhone(array $data): ?string
    {
        return $data['notification_phone']
            ?? $data['guardian_phone']
            ?? $data['guardian_whatsapp']
            ?? $data['father_phone']
            ?? $data['mother_phone']
            ?? $data['student_personal_phone']
            ?? $data['phone']
            ?? null;
    }
}
