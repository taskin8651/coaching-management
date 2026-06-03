<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Batch;
use App\Models\Branch;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
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
}
