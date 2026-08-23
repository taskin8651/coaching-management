<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

trait AppliesErpScope
{
    protected function erpScope(): array
    {
        $user = auth()->user();
        $roles = $user->roles()->pluck('title')->map(fn ($role) => strtolower(trim($role)));

        $teacher = Teacher::where('user_id', $user->id)->first();
        $staff = Staff::where('user_id', $user->id)->first();
        $student = Student::where('user_id', $user->id)->first();
        $parentStudents = Student::where('guardian_user_id', $user->id)->pluck('id');
        $managedBranch = Branch::where('manager_id', $user->id)->first();

        $isAdmin = (bool) $user->is_admin || $roles->contains('admin');
        $isManager = $roles->contains('branch manager') || $roles->contains('manager');
        $isTeacher = $roles->contains('teacher');
        $isStaff = $roles->contains('staff');
        $isStudent = $roles->contains('student');
        $isParent = $roles->contains('parent') || $parentStudents->isNotEmpty();

        $branchId = null;

        if (! $isAdmin) {
            $branchId = $managedBranch->id
                ?? $staff->branch_id
                ?? $teacher->branch_id
                ?? $student->branch_id
                ?? null;
        }

        return [
            'is_admin' => $isAdmin,
            'is_manager' => $isManager,
            'is_teacher' => $isTeacher,
            'is_staff' => $isStaff,
            'is_student' => $isStudent,
            'is_parent' => $isParent,
            'branch_id' => $branchId,
            'teacher_id' => $teacher->id ?? null,
            'staff_id' => $staff->id ?? null,
            'student_id' => $student->id ?? null,
            'parent_student_ids' => $parentStudents,
            'course_id' => $student->course_id ?? null,
            'batch_id' => $student->batch_id ?? null,
        ];
    }

    protected function scopeBranchQuery(Builder $query, ?string $column = 'branch_id'): Builder
    {
        $scope = $this->erpScope();

        if ($scope['is_admin']) {
            return $query;
        }

        return $scope['branch_id']
            ? $query->where($column, $scope['branch_id'])
            : $query->whereRaw('1 = 0');
    }

    protected function scopeStudentQuery(Builder $query, ?string $column = 'id'): Builder
    {
        $scope = $this->erpScope();

        if ($scope['is_admin']) {
            return $query;
        }

        if ($scope['is_student'] && $scope['student_id']) {
            return $query->where($column, $scope['student_id']);
        }

        if ($scope['is_parent'] && $scope['parent_student_ids']->isNotEmpty()) {
            return $query->whereIn($column, $scope['parent_student_ids']);
        }

        if (($scope['is_manager'] || $scope['is_staff']) && $scope['branch_id']) {
            return $query->where('branch_id', $scope['branch_id']);
        }

        if ($scope['is_teacher'] && $scope['teacher_id']) {
            $batchIds = $this->teacherBatchIds($scope['teacher_id']);

            return $batchIds->isNotEmpty()
                ? $query->where(function ($q) use ($batchIds) {
                    $q->whereIn('batch_id', $batchIds)
                        ->orWhereHas('studentBatches', fn ($qq) => $qq->whereIn('batch_id', $batchIds));
                })
                : $query->whereRaw('1 = 0');
        }

        return $query->whereRaw('1 = 0');
    }

    protected function scopeBatchQuery(Builder $query): Builder
    {
        $scope = $this->erpScope();

        if ($scope['is_admin']) {
            return $query;
        }

        if ($scope['is_teacher'] && $scope['teacher_id']) {
            $batchIds = $this->teacherBatchIds($scope['teacher_id']);
            return $batchIds->isNotEmpty() ? $query->whereIn('id', $batchIds) : $query->whereRaw('1 = 0');
        }

        return $scope['branch_id'] ? $query->where('branch_id', $scope['branch_id']) : $query->whereRaw('1 = 0');
    }

    protected function teacherBatchIds(int $teacherId)
    {
        $batchIds = TeacherAssignment::where('teacher_id', $teacherId)
            ->where('status', 'active')
            ->whereNotNull('batch_id')
            ->pluck('batch_id');

        if (Schema::hasColumn('batches', 'teacher_id')) {
            $batchIds = $batchIds->merge(Batch::where('teacher_id', $teacherId)->pluck('id'));
        }

        return $batchIds->unique()->values();
    }

    protected function assertBranchAccess($model, ?string $column = 'branch_id'): void
    {
        $scope = $this->erpScope();

        if ($scope['is_admin']) {
            return;
        }

        abort_if(! $scope['branch_id'] || $model->{$column} != $scope['branch_id'], 403, '403 Forbidden');
    }

    /**
     * Courses grouped by branch_id, for client-side cascading Branch -> Course selects.
     * Shape: { branch_id: [{id, name}, ...] }
     */
    protected function coursesByBranch(): array
    {
        return $this->scopeBranchQuery(Course::where('status', 'active'))
            ->orderBy('name')
            ->get(['id', 'name', 'branch_id'])
            ->groupBy(fn ($course) => $course->branch_id ?: 'none')
            ->map(fn ($courses) => $courses->map(fn ($course) => [
                'id' => $course->id,
                'name' => $course->name,
            ])->values())
            ->toArray();
    }

    /**
     * Batches grouped by branch_id then course_id, for client-side cascading
     * Branch -> Course -> Batch selects.
     * Shape: { branch_id: { course_id_or_'all': [{id, name, course_id}, ...] } }
     */
    protected function batchesByBranchCourse(): array
    {
        return $this->scopeBatchQuery(Batch::where('status', 'active'))
            ->orderBy('name')
            ->get(['id', 'name', 'branch_id', 'course_id'])
            ->groupBy(fn ($batch) => $batch->branch_id ?: 'none')
            ->map(function ($branchBatches) {
                return $branchBatches
                    ->groupBy(fn ($batch) => $batch->course_id ?: 'all')
                    ->map(fn ($batches) => $batches->map(fn ($batch) => [
                        'id' => $batch->id,
                        'name' => $batch->name,
                        'course_id' => $batch->course_id,
                    ])->values())
                    ->toArray();
            })
            ->toArray();
    }

    /**
     * Batches grouped by branch_id only (no course nesting), for forms that filter a batch
     * multi-select purely off a Branch select (e.g. a teacher's repeatable assignment rows).
     * Shape: { branch_id: [{id, name}, ...] }
     */
    protected function batchesByBranch(): array
    {
        return $this->scopeBatchQuery(Batch::where('status', 'active'))
            ->orderBy('name')
            ->get(['id', 'name', 'branch_id'])
            ->groupBy(fn ($batch) => $batch->branch_id ?: 'none')
            ->map(fn ($batches) => $batches->map(fn ($batch) => [
                'id' => $batch->id,
                'name' => $batch->name,
            ])->values())
            ->toArray();
    }

    /**
     * Subjects grouped by branch_id only (no course nesting) — see batchesByBranch() above.
     * Shape: { branch_id: [{id, name}, ...] }
     */
    protected function subjectsByBranch(): array
    {
        return $this->scopeBranchQuery(Subject::where('status', 'active'))
            ->orderBy('name')
            ->get(['id', 'name', 'branch_id'])
            ->groupBy(fn ($subject) => $subject->branch_id ?: 'none')
            ->map(fn ($subjects) => $subjects->map(fn ($subject) => [
                'id' => $subject->id,
                'name' => $subject->name,
            ])->values())
            ->toArray();
    }

    /**
     * Teachers grouped by subject_id, via each teacher's active Teaching Assignments — for
     * client-side cascading Subject -> Teacher selects (e.g. Homework's Academic Mapping card).
     * Shape: { subject_id: [{id, name}, ...] }
     */
    protected function teachersBySubject(): array
    {
        return $this->scopeBranchQuery(TeacherAssignment::where('status', 'active')->whereNotNull('subject_id'))
            ->with('teacher.user')
            ->get()
            ->filter(fn ($assignment) => $assignment->teacher && $assignment->teacher->user)
            ->groupBy('subject_id')
            ->map(fn ($assignments) => $assignments->pluck('teacher')
                ->unique('id')
                ->sortBy(fn ($teacher) => $teacher->user->name)
                ->map(fn ($teacher) => [
                    'id' => $teacher->id,
                    'name' => $teacher->user->name,
                ])->values())
            ->toArray();
    }

    /**
     * "Staff-side" users grouped by branch_id, for client-side cascading Branch -> assignee
     * selects (e.g. "Paid By" on Expenses, "Assigned To" on Maintenance Requests). Only Admin
     * (branch-agnostic, included in every branch's list), Branch Manager and Staff of that
     * specific branch — Teachers/Students/Parents are never valid assignees for these ops
     * screens.
     * Shape: { branch_id: [{id, name}, ...] }
     */
    protected function usersByBranch(): array
    {
        $admins = User::whereHas('roles', fn ($q) => $q->where('id', 1))->get(['id', 'name']);

        $branches = Branch::where('status', 'active')->get(['id', 'manager_id']);

        $staffUsers = Staff::whereHas('user')
            ->with('user:id,name')
            ->get(['id', 'user_id', 'branch_id']);

        return $branches->mapWithKeys(function ($branch) use ($admins, $staffUsers) {
            $branchUsers = $admins->values();

            if ($branch->manager_id) {
                $manager = User::find($branch->manager_id, ['id', 'name']);

                if ($manager) {
                    $branchUsers = $branchUsers->push($manager);
                }
            }

            $branchStaffUsers = $staffUsers->where('branch_id', $branch->id)->pluck('user')->filter();
            $branchUsers = $branchUsers->merge($branchStaffUsers);

            return [
                $branch->id => $branchUsers->unique('id')->sortBy('name')->map(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                ])->values()->toArray(),
            ];
        })->toArray();
    }

    /**
     * The course a batch belongs to, for client-side cascading Batch -> Course selects
     * (each batch has exactly one course, so the list is 0 or 1 items).
     * Shape: { batch_id: [{id, name}] }
     */
    protected function coursesByBatch(): array
    {
        return $this->scopeBatchQuery(Batch::where('status', 'active'))
            ->with(['course' => fn ($query) => $query->where('status', 'active')])
            ->get(['id', 'course_id'])
            ->mapWithKeys(fn ($batch) => [
                $batch->id => $batch->course
                    ? [['id' => $batch->course->id, 'name' => $batch->course->name]]
                    : [],
            ])
            ->toArray();
    }

    /**
     * Subjects available per batch (via that batch's branch + course), for client-side cascading
     * Batch -> Subject selects on forms that only expose a single Batch select (no separate
     * Branch/Course selects to cascade from).
     * Shape: { batch_id: [{id, name, course_id}, ...] }
     */
    protected function subjectsByBatch(): array
    {
        $batches = $this->scopeBatchQuery(Batch::where('status', 'active'))->get(['id', 'branch_id', 'course_id']);
        $subjectsByBranchCourse = $this->subjectsByBranchCourse();

        return $batches->mapWithKeys(function ($batch) use ($subjectsByBranchCourse) {
            $branchBucket = $subjectsByBranchCourse[$batch->branch_id] ?? [];
            $common = $branchBucket['all'] ?? [];
            $specific = $batch->course_id ? ($branchBucket[$batch->course_id] ?? []) : [];

            $merged = collect($common)->merge($specific)->unique('id')->values()->all();

            return [$batch->id => $merged];
        })->toArray();
    }

    /**
     * Subjects grouped by branch_id then course_id, for client-side cascading
     * Branch -> Course -> Subject selects.
     * Shape: { branch_id: { course_id_or_'all': [{id, name, course_id}, ...] } }
     */
    protected function subjectsByBranchCourse(): array
    {
        return $this->scopeBranchQuery(Subject::where('status', 'active'))
            ->orderBy('name')
            ->get(['id', 'name', 'branch_id', 'course_id'])
            ->groupBy(fn ($subject) => $subject->branch_id ?: 'none')
            ->map(function ($branchSubjects) {
                return $branchSubjects
                    ->groupBy(fn ($subject) => $subject->course_id ?: 'all')
                    ->map(fn ($subjects) => $subjects->map(fn ($subject) => [
                        'id' => $subject->id,
                        'name' => $subject->name,
                        'course_id' => $subject->course_id,
                    ])->values())
                    ->toArray();
            })
            ->toArray();
    }
}
