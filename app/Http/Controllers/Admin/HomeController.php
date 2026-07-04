<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Enquiry;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Expense;
use App\Models\FeePayment;
use App\Models\Homework;
use App\Models\Notice;
use App\Models\SalaryPayment;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentBatch;
use App\Models\StudyMaterial;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\Timetable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $scope = $this->resolveScope();
        $batchIds = $this->batchIdsForScope($scope);
        $studentIds = $this->studentIdsForScope($scope, $batchIds);

        $studentsQuery = $this->scopeStudents(Student::query(), $scope, $studentIds);
        $teachersQuery = $this->scopeBranch(Teacher::query(), $scope);
        $batchesQuery = $this->scopeBatches(Batch::query(), $scope, $batchIds);
        $coursesQuery = $this->scopeBranch(Course::query(), $scope);
        $subjectsQuery = $this->scopeBranch(Subject::query(), $scope);
        $enquiriesQuery = $this->scopeEnquiries(Enquiry::query(), $scope);
        $feeQuery = $this->scopeFinance(FeePayment::query()->where('payment_status', '!=', 'cancelled'), $scope, $studentIds);
        $attendanceQuery = $this->scopeAttendance(StudentAttendance::query(), $scope, $studentIds, $batchIds);
        $examQuery = $this->scopeAcademic(Exam::query(), $scope, $batchIds);
        $noticeQuery = $this->scopeNotice(Notice::query()->where('status', 'published'), $scope, $batchIds);
        $studyMaterialQuery = $this->scopeAcademic(StudyMaterial::query()->where('status', 'active'), $scope, $batchIds);
        $timetableQuery = $this->scopeTimetables(Timetable::query(), $scope, $batchIds);
        $homeworkQuery = $this->scopeAcademic(Homework::query(), $scope, $batchIds);

        $totalStudents = (clone $studentsQuery)->count();
        $totalTeachers = $scope['is_student'] ? 0 : (clone $teachersQuery)->count();
        $activeBatches = (clone $batchesQuery)->where('status', 'active')->count();
        $totalCourses = $scope['is_student'] ? (int) filled($scope['course_id']) : (clone $coursesQuery)->count();
        $totalSubjects = (clone $subjectsQuery)->count();
        $newEnquiries = (clone $enquiriesQuery)->where('status', 'new')->count();
        $totalEnquiries = (clone $enquiriesQuery)->count();

        $feeCollected = (clone $feeQuery)->sum('paid_amount');
        $pendingFees = (clone $feeQuery)->sum('due_amount');
        $feeTotal = max($feeCollected + $pendingFees, 1);
        $feePaidPercent = round(($feeCollected / $feeTotal) * 100, 1);
        $feeDuePercent = round(($pendingFees / $feeTotal) * 100, 1);

        $todayAttendance = (clone $attendanceQuery)->whereDate('attendance_date', $today);
        $attendanceTotalToday = (clone $todayAttendance)->count();
        $presentToday = (clone $todayAttendance)->where('status', 'present')->count();
        $lateToday = (clone $todayAttendance)->where('status', 'late')->count();
        $absentToday = (clone $todayAttendance)->where('status', 'absent')->count();
        $attendancePercent = $attendanceTotalToday ? round(($presentToday / $attendanceTotalToday) * 100, 1) : 0;
        $latePercent = $attendanceTotalToday ? round(($lateToday / $attendanceTotalToday) * 100, 1) : 0;
        $absentPercent = $attendanceTotalToday ? round(($absentToday / $attendanceTotalToday) * 100, 1) : 0;

        $expensesQuery = Expense::query()->where('status', 'paid');
        $expensesQuery = ($scope['is_student'] || $scope['is_teacher'])
            ? $expensesQuery->whereRaw('1 = 0')
            : $this->scopeBranch($expensesQuery, $scope);

        $salaryQuery = $this->scopeSalary(SalaryPayment::query()->where('payment_status', '!=', 'cancelled'), $scope);
        $expenses = (clone $expensesQuery)->sum('amount');
        $salaryPaid = (clone $salaryQuery)->sum('paid_amount');
        $salaryDue = (clone $salaryQuery)->sum('due_amount');
        $netBalance = $feeCollected - $expenses - $salaryPaid;

        $monthlyFee = $this->monthlySeries(
            $this->scopeFinance(FeePayment::query()->where('payment_status', '!=', 'cancelled'), $scope, $studentIds),
            'payment_date',
            'paid_amount'
        );
        $monthlyEnquiries = $this->monthlyCountSeries((clone $enquiriesQuery), 'created_at');

        $recentAdmissions = $this->scopeAdmissions(Admission::with(['student.user', 'course', 'batch']), $scope, $studentIds)
            ->latest()
            ->take(5)
            ->get();

        $recentFeePayments = (clone $feeQuery)
            ->with(['student.user', 'batch'])
            ->latest()
            ->take(5)
            ->get();

        $latestNotices = (clone $noticeQuery)
            ->with(['branch', 'course', 'batch'])
            ->latest()
            ->take(4)
            ->get();

        $todayClasses = (clone $timetableQuery)
            ->with(['batch', 'subject', 'teacher.user'])
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($today) {
                $query->whereDate('schedule_date', $today)
                    ->orWhere(function ($q) use ($today) {
                        $q->whereNull('schedule_date')
                            ->whereRaw('LOWER(day_of_week) = ?', [strtolower($today->format('l'))]);
                    });
            })
            ->orderBy('start_time')
            ->take(6)
            ->get();

        $upcomingExams = (clone $examQuery)
            ->with(['batch', 'subject'])
            ->where('status', 'scheduled')
            ->whereDate('exam_date', '>=', $today)
            ->orderBy('exam_date')
            ->take(5)
            ->get();

        $recentMaterials = (clone $studyMaterialQuery)
            ->with(['batch', 'subject'])
            ->latest()
            ->take(5)
            ->get();

        $pendingHomeworkCount = (clone $homeworkQuery)
            ->where('status', 'active')
            ->where(function ($query) use ($today) {
                $query->whereNull('due_date')->orWhereDate('due_date', '>=', $today);
            })
            ->count();

        $examAverage = $this->examAverage($scope, $studentIds, $batchIds);

        $topCards = [
            ['title' => $scope['is_student'] ? 'My Profile' : 'Total Students', 'value' => $scope['is_student'] ? 1 : $totalStudents, 'icon' => 'fas fa-users', 'tone' => 'blue', 'show' => true],
            ['title' => 'Total Teachers', 'value' => $totalTeachers, 'icon' => 'fas fa-user-tie', 'tone' => 'green', 'show' => ! $scope['is_student']],
            ['title' => 'Active Batches', 'value' => $activeBatches, 'icon' => 'fas fa-users-rectangle', 'tone' => 'purple', 'show' => true],
            ['title' => 'Courses', 'value' => $totalCourses, 'icon' => 'fas fa-book-open', 'tone' => 'amber', 'show' => true],
            ['title' => 'New Enquiries', 'value' => $newEnquiries, 'icon' => 'fas fa-circle-question', 'tone' => 'cyan', 'show' => ! $scope['is_student'] && ! $scope['is_teacher']],
            ['title' => 'Fee Collected', 'value' => '₹' . number_format($feeCollected, 0), 'icon' => 'fas fa-indian-rupee-sign', 'tone' => 'emerald', 'show' => ! $scope['is_teacher']],
            ['title' => 'Pending Fees', 'value' => '₹' . number_format($pendingFees, 0), 'icon' => 'fas fa-wallet', 'tone' => 'orange', 'show' => ! $scope['is_teacher']],
            ['title' => 'Attendance Today', 'value' => $attendancePercent . '%', 'icon' => 'fas fa-calendar-check', 'tone' => 'pink', 'show' => true],
        ];

        $activityFeed = collect()
            ->merge($recentAdmissions->map(fn ($item) => [
                'icon' => 'fas fa-user-plus',
                'tone' => 'green',
                'title' => 'New admission: ' . ($item->student->user->name ?? 'Student'),
                'meta' => optional($item->created_at)->format('h:i A') ?? '',
            ]))
            ->merge($recentFeePayments->take(3)->map(fn ($item) => [
                'icon' => 'fas fa-indian-rupee-sign',
                'tone' => 'blue',
                'title' => 'Fee received: ₹' . number_format($item->paid_amount, 0),
                'meta' => $item->student->user->name ?? 'Student',
            ]))
            ->merge((clone $enquiriesQuery)->latest()->take(3)->get()->map(fn ($item) => [
                'icon' => 'fas fa-headset',
                'tone' => 'purple',
                'title' => 'New enquiry: ' . ($item->student_name ?? 'Enquiry'),
                'meta' => $item->course->name ?? $item->phone ?? '',
            ]))
            ->take(6)
            ->values();

        return view('home', compact(
            'scope',
            'topCards',
            'totalSubjects',
            'totalEnquiries',
            'feeCollected',
            'pendingFees',
            'feePaidPercent',
            'feeDuePercent',
            'attendanceTotalToday',
            'presentToday',
            'lateToday',
            'absentToday',
            'attendancePercent',
            'latePercent',
            'absentPercent',
            'expenses',
            'salaryPaid',
            'salaryDue',
            'netBalance',
            'monthlyFee',
            'monthlyEnquiries',
            'recentAdmissions',
            'recentFeePayments',
            'latestNotices',
            'todayClasses',
            'upcomingExams',
            'recentMaterials',
            'pendingHomeworkCount',
            'examAverage',
            'activityFeed'
        ));
    }

    private function resolveScope(): array
    {
        $user = auth()->user();
        $roles = $user->roles()->pluck('title')->map(fn ($role) => strtolower(trim($role)));

        $teacher = Teacher::where('user_id', $user->id)->first();
        $staff = Staff::where('user_id', $user->id)->first();
        $student = Student::where('user_id', $user->id)->first();
        $managerBranch = Branch::where('manager_id', $user->id)->first();

        $isAdmin = (bool) $user->is_admin || $roles->contains('admin');
        $isManager = $roles->contains('branch manager') || $roles->contains('manager');
        $isTeacher = $roles->contains('teacher');
        $isStaff = $roles->contains('staff');
        $isStudent = $roles->contains('student');

        $branchId = null;

        if (! $isAdmin) {
            $branchId = $managerBranch->id
                ?? $staff->branch_id
                ?? $teacher->branch_id
                ?? $student->branch_id
                ?? null;
        }

        return [
            'role_label' => $isAdmin ? 'Admin / Super Admin' : ($isManager ? 'Branch Manager' : ($isTeacher ? 'Teacher' : ($isStudent ? 'Student' : ($isStaff ? 'Staff' : 'User')))),
            'is_admin' => $isAdmin,
            'is_manager' => $isManager,
            'is_teacher' => $isTeacher,
            'is_staff' => $isStaff,
            'is_student' => $isStudent,
            'branch_id' => $branchId,
            'teacher_id' => $teacher->id ?? null,
            'staff_id' => $staff->id ?? null,
            'student_id' => $student->id ?? null,
            'course_id' => $student->course_id ?? null,
            'batch_id' => $student->batch_id ?? null,
        ];
    }

    private function batchIdsForScope(array $scope): ?Collection
    {
        if ($scope['is_admin']) {
            return null;
        }

        if ($scope['is_student']) {
            $ids = collect([$scope['batch_id']])->filter();

            if ($scope['student_id']) {
                $ids = $ids->merge(
                    StudentBatch::where('student_id', $scope['student_id'])
                        ->where('status', 'active')
                        ->pluck('batch_id')
                );
            }

            return $ids->unique()->values();
        }

        if ($scope['is_teacher'] && $scope['teacher_id']) {
            $ids = TeacherAssignment::where('teacher_id', $scope['teacher_id'])
                ->where('status', 'active')
                ->whereNotNull('batch_id')
                ->pluck('batch_id');

            $ids = $ids->merge(Timetable::where('teacher_id', $scope['teacher_id'])->pluck('batch_id'));

            return $ids->filter()->unique()->values();
        }

        if ($scope['branch_id']) {
            return Batch::where('branch_id', $scope['branch_id'])->pluck('id');
        }

        return collect([]);
    }

    private function studentIdsForScope(array $scope, ?Collection $batchIds): ?Collection
    {
        if ($scope['is_admin']) {
            return null;
        }

        if ($scope['is_student'] && $scope['student_id']) {
            return collect([$scope['student_id']]);
        }

        if ($scope['is_teacher']) {
            return $batchIds && $batchIds->isNotEmpty()
                ? Student::where(function ($query) use ($batchIds) {
                    $query->whereIn('batch_id', $batchIds)
                        ->orWhereHas('studentBatches', fn ($q) => $q->whereIn('batch_id', $batchIds)->where('status', 'active'));
                })->pluck('id')->unique()->values()
                : collect([]);
        }

        if ($scope['branch_id']) {
            return Student::where('branch_id', $scope['branch_id'])->pluck('id');
        }

        return collect([]);
    }

    private function scopeBranch(Builder $query, array $scope, string $column = 'branch_id'): Builder
    {
        if ($scope['is_admin']) {
            return $query;
        }

        return $scope['branch_id'] ? $query->where($column, $scope['branch_id']) : $query->whereRaw('1 = 0');
    }

    private function scopeStudents(Builder $query, array $scope, ?Collection $studentIds): Builder
    {
        if ($scope['is_admin']) {
            return $query;
        }

        return $studentIds && $studentIds->isNotEmpty()
            ? $query->whereIn('id', $studentIds)
            : $query->whereRaw('1 = 0');
    }

    private function scopeBatches(Builder $query, array $scope, ?Collection $batchIds): Builder
    {
        if ($scope['is_admin']) {
            return $query;
        }

        return $batchIds && $batchIds->isNotEmpty()
            ? $query->whereIn('id', $batchIds)
            : $query->whereRaw('1 = 0');
    }

    private function scopeFinance(Builder $query, array $scope, ?Collection $studentIds): Builder
    {
        if ($scope['is_admin']) {
            return $query;
        }

        if ($studentIds && $studentIds->isNotEmpty()) {
            return $query->whereIn('student_id', $studentIds);
        }

        return $scope['branch_id'] ? $query->where('branch_id', $scope['branch_id']) : $query->whereRaw('1 = 0');
    }

    private function scopeAttendance(Builder $query, array $scope, ?Collection $studentIds, ?Collection $batchIds): Builder
    {
        if ($scope['is_admin']) {
            return $query;
        }

        if ($scope['is_student'] || $scope['is_teacher']) {
            return $studentIds && $studentIds->isNotEmpty()
                ? $query->whereIn('student_id', $studentIds)
                : $query->whereRaw('1 = 0');
        }

        return $batchIds && $batchIds->isNotEmpty()
            ? $query->whereIn('batch_id', $batchIds)
            : $query->whereRaw('1 = 0');
    }

    private function scopeAcademic(Builder $query, array $scope, ?Collection $batchIds): Builder
    {
        if ($scope['is_admin']) {
            return $query;
        }

        if (($scope['is_teacher'] || $scope['is_student']) && $batchIds && $batchIds->isNotEmpty()) {
            return $query->where(function ($q) use ($batchIds, $scope) {
                $q->whereIn('batch_id', $batchIds);

                if ($scope['branch_id']) {
                    $q->orWhere(function ($qq) use ($scope) {
                        $qq->whereNull('batch_id')->where('branch_id', $scope['branch_id']);
                    });
                }
            });
        }

        return $this->scopeBranch($query, $scope);
    }

    private function scopeTimetables(Builder $query, array $scope, ?Collection $batchIds): Builder
    {
        if ($scope['is_admin']) {
            return $query;
        }

        if ($scope['is_teacher'] && $scope['teacher_id']) {
            return $query->where('teacher_id', $scope['teacher_id']);
        }

        if (($scope['is_student'] || $scope['is_manager'] || $scope['is_staff']) && $batchIds && $batchIds->isNotEmpty()) {
            return $query->whereIn('batch_id', $batchIds);
        }

        return $scope['branch_id'] ? $query->where('branch_id', $scope['branch_id']) : $query->whereRaw('1 = 0');
    }

    private function scopeNotice(Builder $query, array $scope, ?Collection $batchIds): Builder
    {
        if ($scope['is_admin']) {
            return $query;
        }

        return $query->where(function ($q) use ($scope, $batchIds) {
            $q->where('target_audience', 'all');

            if ($scope['is_student']) {
                $q->orWhere('target_audience', 'students');
            } elseif ($scope['is_teacher']) {
                $q->orWhere('target_audience', 'teachers');
            } elseif ($scope['is_staff']) {
                $q->orWhere('target_audience', 'staff');
            } elseif ($scope['is_manager']) {
                $q->orWhere('target_audience', 'managers');
            }

            if ($scope['branch_id']) {
                $q->orWhere(fn ($qq) => $qq->where('target_audience', 'branch')->where('branch_id', $scope['branch_id']));
            }

            if ($scope['course_id']) {
                $q->orWhere(fn ($qq) => $qq->where('target_audience', 'course')->where('course_id', $scope['course_id']));
            }

            if ($batchIds && $batchIds->isNotEmpty()) {
                $q->orWhere(fn ($qq) => $qq->where('target_audience', 'batch')->whereIn('batch_id', $batchIds));
            }
        });
    }

    private function scopeEnquiries(Builder $query, array $scope): Builder
    {
        if ($scope['is_student'] || $scope['is_teacher']) {
            return $query->whereRaw('1 = 0');
        }

        return $this->scopeBranch($query, $scope);
    }

    private function scopeAdmissions(Builder $query, array $scope, ?Collection $studentIds): Builder
    {
        if ($scope['is_admin']) {
            return $query;
        }

        if ($studentIds && $studentIds->isNotEmpty()) {
            return $query->whereIn('student_id', $studentIds);
        }

        return $scope['branch_id'] ? $query->where('branch_id', $scope['branch_id']) : $query->whereRaw('1 = 0');
    }

    private function scopeSalary(Builder $query, array $scope): Builder
    {
        if ($scope['is_admin']) {
            return $query;
        }

        if ($scope['is_teacher'] && $scope['teacher_id']) {
            return $query->where('teacher_id', $scope['teacher_id']);
        }

        if ($scope['is_staff'] && $scope['staff_id']) {
            return $query->where('staff_id', $scope['staff_id']);
        }

        if ($scope['is_student']) {
            return $query->whereRaw('1 = 0');
        }

        return $this->scopeBranch($query, $scope);
    }

    private function monthlySeries(Builder $query, string $dateColumn, string $sumColumn): Collection
    {
        $data = $query
            ->whereYear($dateColumn, now()->year)
            ->selectRaw("MONTH($dateColumn) as month, SUM($sumColumn) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        return collect(range(1, 12))->map(fn ($month) => [
            'label' => Carbon::create()->month($month)->format('M'),
            'value' => (float) ($data[$month] ?? 0),
        ]);
    }

    private function monthlyCountSeries(Builder $query, string $dateColumn): Collection
    {
        $data = $query
            ->whereYear($dateColumn, now()->year)
            ->selectRaw("MONTH($dateColumn) as month, COUNT(*) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        return collect(range(1, 12))->map(fn ($month) => [
            'label' => Carbon::create()->month($month)->format('M'),
            'value' => (int) ($data[$month] ?? 0),
        ]);
    }

    private function examAverage(array $scope, ?Collection $studentIds, ?Collection $batchIds): float
    {
        $query = ExamResult::query();

        if ($scope['is_student'] && $scope['student_id']) {
            $query->where('student_id', $scope['student_id']);
        } elseif ($scope['is_teacher'] && $batchIds && $batchIds->isNotEmpty()) {
            $query->whereHas('exam', fn ($q) => $q->whereIn('batch_id', $batchIds));
        } elseif (! $scope['is_admin']) {
            if ($studentIds && $studentIds->isNotEmpty()) {
                $query->whereIn('student_id', $studentIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return round((float) $query->avg('percentage'), 1);
    }
}
