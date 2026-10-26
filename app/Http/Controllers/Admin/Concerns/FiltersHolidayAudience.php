<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Holiday;
use App\Models\Student;
use App\Models\StudentBatch;
use Illuminate\Support\Collection;

trait FiltersHolidayAudience
{
    protected function filterHolidaysForViewer(Collection $holidays, array $scope, ?Collection $selectedStudentIds = null): Collection
    {
        if ($scope['is_admin'] || $scope['is_manager']) return $holidays;

        $studentIds = $selectedStudentIds ?? collect();
        if ($selectedStudentIds === null) {
            if ($scope['is_student'] && $scope['student_id']) $studentIds->push($scope['student_id']);
            if ($scope['is_parent']) $studentIds = $studentIds->merge($scope['parent_student_ids']);
        }

        $studentIds = $studentIds->unique()->values();
        $studentBatchIds = collect();
        if ($studentIds->isNotEmpty()) {
            $studentBatchIds = Student::whereIn('id', $studentIds)->whereNotNull('batch_id')->pluck('batch_id')
                ->merge(StudentBatch::whereIn('student_id', $studentIds)->where('status', 'active')->pluck('batch_id'))
                ->unique()->values();
        }

        return $holidays->filter(fn (Holiday $holiday) => $this->holidayTargetsViewer($holiday, $scope, $studentIds, $studentBatchIds))->values();
    }

    private function holidayTargetsViewer(Holiday $holiday, array $scope, Collection $studentIds, Collection $studentBatchIds): bool
    {
        // Preserve visibility for legacy holidays created before audience targeting was added.
        if (! $holiday->target_mode) return true;

        $audiences = $holiday->audiences ?? [];
        if (is_string($audiences)) $audiences = json_decode($audiences, true) ?: [];
        $types = [];
        if ($scope['is_student'] || $scope['is_parent']) $types[] = 'students';
        if ($scope['is_teacher']) $types[] = 'teachers';
        if ($scope['is_staff']) $types[] = 'staff';
        if (! array_intersect((array) $audiences, $types)) return false;

        $ids = fn (string $type) => $holiday->targets->where('target_type', $type)->pluck('target_id')->map(fn ($id) => (int) $id);

        return match ($holiday->target_mode) {
            'all_branches' => true,
            'branch_wise' => $scope['branch_id'] && $ids('branch')->contains((int) $scope['branch_id']),
            'batch_wise' => ($scope['is_student'] || $scope['is_parent']) && $studentBatchIds->intersect($ids('batch'))->isNotEmpty(),
            'selected_students' => ($scope['is_student'] || $scope['is_parent']) && $studentIds->intersect($ids('student'))->isNotEmpty(),
            'selected_teachers', 'teacher_wise' => $scope['is_teacher'] && $ids('teacher')->contains((int) $scope['teacher_id']),
            'all_branch_teachers' => $scope['is_teacher'],
            'single_branch_teachers' => $scope['is_teacher'] && $holiday->branch_id == $scope['branch_id'] && $ids('teacher')->contains((int) $scope['teacher_id']),
            'all_branch_staff' => $scope['is_staff'],
            'single_branch_staff' => $scope['is_staff'] && $holiday->branch_id == $scope['branch_id'] && $ids('staff')->contains((int) $scope['staff_id']),
            'selected_staff' => $scope['is_staff'] && $ids('staff')->contains((int) $scope['staff_id']),
            default => false,
        };
    }
}
