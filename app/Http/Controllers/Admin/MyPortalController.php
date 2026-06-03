<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Models\FacultyLogBook;
use App\Models\FeeInstallment;
use App\Models\Homework;
use App\Models\ReportCard;
use App\Models\SalaryPayment;
use App\Models\StudentAttendance;
use App\Models\StudentRemark;
use App\Models\StudyMaterial;
use App\Models\Student;
use App\Models\Timetable;
use Illuminate\Support\Collection;

class MyPortalController extends Controller
{
    use AppliesErpScope;

    public function index()
    {
        $scope = $this->erpScope();
        $studentIds = $this->visibleStudentIds($scope);

        $studentAttendances = StudentAttendance::with(['student.user', 'batch', 'subject'])
            ->when($studentIds->isNotEmpty(), fn ($q) => $q->whereIn('student_id', $studentIds), fn ($q) => $q->whereRaw('1 = 0'))
            ->latest('attendance_date')
            ->take(20)
            ->get();

        $homeworks = Homework::with(['batch', 'subject', 'teacher.user', 'submissions'])
            ->when($studentIds->isNotEmpty(), fn ($q) => $q->whereHas('submissions', fn ($qq) => $qq->whereIn('student_id', $studentIds)))
            ->when($scope['is_teacher'] && $scope['teacher_id'], fn ($q) => $q->orWhere('teacher_id', $scope['teacher_id']))
            ->when(! $scope['is_admin'] && ! $scope['is_teacher'] && $studentIds->isEmpty(), fn ($q) => $q->whereRaw('1 = 0'))
            ->latest()
            ->take(20)
            ->get();

        $feeInstallments = FeeInstallment::with(['student.user'])
            ->when($studentIds->isNotEmpty(), fn ($q) => $q->whereIn('student_id', $studentIds), fn ($q) => $q->whereRaw('1 = 0'))
            ->latest()
            ->take(20)
            ->get();

        $remarks = StudentRemark::with(['student.user', 'teacher.user'])
            ->when($studentIds->isNotEmpty(), fn ($q) => $q->whereIn('student_id', $studentIds)->where('visible_to_parent', true), fn ($q) => $q->whereRaw('1 = 0'))
            ->latest('remark_date')
            ->take(20)
            ->get();

        $reportCards = ReportCard::with(['student.user', 'exam', 'batch'])
            ->when($studentIds->isNotEmpty(), fn ($q) => $q->whereIn('student_id', $studentIds)->where('published_to_parent', true), fn ($q) => $q->whereRaw('1 = 0'))
            ->latest()
            ->take(20)
            ->get();

        $visibleBatchIds = $this->visibleBatchIds($scope, $studentIds);

        $studyMaterials = StudyMaterial::with(['batch', 'subject'])
            ->when($studentIds->isNotEmpty(), function ($q) use ($scope, $visibleBatchIds) {
                $q->where(function ($qq) use ($scope) {
                    $qq->whereNull('branch_id')->orWhere('branch_id', $scope['branch_id']);
                })->where(function ($qq) use ($visibleBatchIds) {
                    $qq->whereNull('batch_id');
                    if ($visibleBatchIds->isNotEmpty()) {
                        $qq->orWhereIn('batch_id', $visibleBatchIds);
                    }
                });
            })
            ->when($scope['is_teacher'] && $scope['teacher_id'], fn ($q) => $q->orWhere('uploaded_by_id', auth()->id()))
            ->when(! $scope['is_admin'] && ! $scope['is_teacher'] && $studentIds->isEmpty(), fn ($q) => $q->whereRaw('1 = 0'))
            ->latest()
            ->take(20)
            ->get();

        $teacherTimetables = collect();
        $facultyLogs = collect();
        $salaryPayments = collect();

        if ($scope['is_teacher'] && $scope['teacher_id']) {
            $teacherTimetables = Timetable::with(['batch', 'subject'])
                ->where('teacher_id', $scope['teacher_id'])
                ->latest()
                ->take(20)
                ->get();

            $facultyLogs = FacultyLogBook::with(['batch', 'subject'])
                ->where('teacher_id', $scope['teacher_id'])
                ->latest('lecture_date')
                ->take(20)
                ->get();

            $salaryPayments = SalaryPayment::where('teacher_id', $scope['teacher_id'])
                ->latest()
                ->take(12)
                ->get();
        }

        return view('admin.myPortal.index', compact(
            'scope',
            'studentAttendances',
            'homeworks',
            'feeInstallments',
            'remarks',
            'reportCards',
            'studyMaterials',
            'teacherTimetables',
            'facultyLogs',
            'salaryPayments'
        ));
    }

    private function visibleStudentIds(array $scope): Collection
    {
        if ($scope['is_student'] && $scope['student_id']) {
            return collect([$scope['student_id']]);
        }

        if ($scope['is_parent']) {
            return $scope['parent_student_ids'];
        }

        return collect();
    }

    private function visibleBatchIds(array $scope, Collection $studentIds): Collection
    {
        if ($studentIds->isEmpty()) {
            return collect();
        }

        return Student::whereIn('id', $studentIds)
            ->with('studentBatches')
            ->get()
            ->flatMap(function ($student) {
                return collect([$student->batch_id])
                    ->merge($student->studentBatches->where('status', 'active')->pluck('batch_id'));
            })
            ->filter()
            ->unique()
            ->values();
    }
}
