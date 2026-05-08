<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Enquiry;
use App\Models\Exam;
use App\Models\Expense;
use App\Models\FeePayment;
use App\Models\Notice;
use App\Models\SalaryPayment;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudyMaterial;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function index()
    {
        $user  = auth()->user();
        $today = Carbon::today();

        $scope = $this->resolveScope($user);
        $studentIds = $this->studentIdsForScope($scope);

        /*
        |--------------------------------------------------------------------------
        | Main Counts
        |--------------------------------------------------------------------------
        */

        $totalBranches = $scope['is_admin']
            ? Branch::count()
            : ($scope['branch_id'] ? 1 : 0);

        $totalCourses = $this->applyBranchScope(Course::query(), $scope)->count();
        $totalSubjects = $this->applyBranchScope(Subject::query(), $scope)->count();
        $totalBatches = $this->applyBranchScope(Batch::query(), $scope)->count();

        $totalStudents = $this->applyStudentScope(Student::query(), $scope, $studentIds)->count();
        $totalTeachers = $this->applyBranchScope(Teacher::query(), $scope)->count();
        $totalStaff = $this->applyBranchScope(Staff::query(), $scope)->count();

        $totalUsers = $scope['is_admin'] ? User::count() : 0;

        /*
        |--------------------------------------------------------------------------
        | Enquiries
        |--------------------------------------------------------------------------
        */

        $enquiryQuery = $this->applyBranchScope(Enquiry::query(), $scope);

        if ($scope['is_student']) {
            $enquiryQuery->whereRaw('1 = 0');
        }

        $totalEnquiries = (clone $enquiryQuery)->count();
        $newEnquiries = (clone $enquiryQuery)->where('status', 'new')->count();
        $convertedEnquiries = (clone $enquiryQuery)->where('status', 'converted')->count();

        $pendingFollowUps = (clone $enquiryQuery)
            ->whereNotNull('next_follow_up_date')
            ->whereDate('next_follow_up_date', '<=', $today)
            ->whereNotIn('status', ['converted', 'rejected', 'not_interested'])
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Finance
        |--------------------------------------------------------------------------
        */

        $feeQuery = FeePayment::query()->where('payment_status', '!=', 'cancelled');
        $feeQuery = $this->applyFinanceScope($feeQuery, $scope, $studentIds);

        $totalFeeCollection = (clone $feeQuery)->sum('paid_amount');
        $totalFeeDue = (clone $feeQuery)->sum('due_amount');

        $expenseQuery = Expense::query()->where('status', 'paid');

        if ($scope['is_student'] || $scope['is_teacher']) {
            $expenseQuery->whereRaw('1 = 0');
        } else {
            $expenseQuery = $this->applyBranchScope($expenseQuery, $scope);
        }

        $totalExpenses = (clone $expenseQuery)->sum('amount');

        $salaryQuery = SalaryPayment::query()->where('payment_status', '!=', 'cancelled');

        if ($scope['is_student']) {
            $salaryQuery->whereRaw('1 = 0');
        } elseif ($scope['is_teacher'] && $scope['teacher_id']) {
            $salaryQuery->where('teacher_id', $scope['teacher_id']);
        } elseif ($scope['is_staff'] && $scope['staff_id']) {
            $salaryQuery->where('staff_id', $scope['staff_id']);
        } else {
            $salaryQuery = $this->applyBranchScope($salaryQuery, $scope);
        }

        $totalSalaryPaid = (clone $salaryQuery)->sum('paid_amount');
        $totalSalaryDue = (clone $salaryQuery)->sum('due_amount');

        $netBalance = $totalFeeCollection - $totalExpenses - $totalSalaryPaid;

        /*
        |--------------------------------------------------------------------------
        | Academic / Exams / Materials / Notices
        |--------------------------------------------------------------------------
        */

        $examQuery = $this->applyAcademicScope(Exam::query(), $scope);

        $totalExams = (clone $examQuery)->count();
        $completedExams = (clone $examQuery)->where('status', 'completed')->count();
        $upcomingExamsCount = (clone $examQuery)
            ->where('status', 'scheduled')
            ->whereDate('exam_date', '>=', $today)
            ->count();

        $studyMaterialQuery = $this->applyAcademicScope(StudyMaterial::query(), $scope)
            ->where('status', 'active');

        $totalStudyMaterials = (clone $studyMaterialQuery)->count();

        $noticeQuery = $this->applyNoticeScope(Notice::query(), $scope)
            ->where('status', 'published');

        $totalNotices = (clone $noticeQuery)->count();

        /*
        |--------------------------------------------------------------------------
        | Recent Records
        |--------------------------------------------------------------------------
        */

        $recentFeePayments = (clone $feeQuery)
            ->with(['student.user', 'branch', 'course'])
            ->latest()
            ->take(6)
            ->get();

        $recentEnquiries = (clone $enquiryQuery)
            ->with(['branch', 'course', 'assignedTo'])
            ->latest()
            ->take(6)
            ->get();

        $upcomingExams = (clone $examQuery)
            ->with(['branch', 'course', 'batch', 'subject'])
            ->where('status', 'scheduled')
            ->whereDate('exam_date', '>=', $today)
            ->orderBy('exam_date', 'asc')
            ->take(6)
            ->get();

        $recentStudyMaterials = (clone $studyMaterialQuery)
            ->with(['branch', 'course', 'batch', 'subject', 'uploadedBy'])
            ->latest()
            ->take(6)
            ->get();

        $recentNotices = (clone $noticeQuery)
            ->with(['branch', 'course', 'batch', 'createdBy'])
            ->latest()
            ->take(6)
            ->get();

        $myStudents = $this->applyStudentScope(Student::query(), $scope, $studentIds)
            ->with(['user', 'branch', 'course', 'batch'])
            ->latest()
            ->take(8)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Monthly Finance Chart
        |--------------------------------------------------------------------------
        */

        $monthlyFeeCollection = $this->applyFinanceScope(
            FeePayment::query()->where('payment_status', '!=', 'cancelled'),
            $scope,
            $studentIds
        )
            ->whereYear('payment_date', now()->year)
            ->selectRaw('MONTH(payment_date) as month, SUM(paid_amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        $monthlyExpenses = Expense::query()->where('status', 'paid');

        if ($scope['is_student'] || $scope['is_teacher']) {
            $monthlyExpenses->whereRaw('1 = 0');
        } else {
            $monthlyExpenses = $this->applyBranchScope($monthlyExpenses, $scope);
        }

        $monthlyExpenses = $monthlyExpenses
            ->whereYear('expense_date', now()->year)
            ->selectRaw('MONTH(expense_date) as month, SUM(amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        $monthlySalary = SalaryPayment::query()->where('payment_status', '!=', 'cancelled');

        if ($scope['is_student']) {
            $monthlySalary->whereRaw('1 = 0');
        } elseif ($scope['is_teacher'] && $scope['teacher_id']) {
            $monthlySalary->where('teacher_id', $scope['teacher_id']);
        } elseif ($scope['is_staff'] && $scope['staff_id']) {
            $monthlySalary->where('staff_id', $scope['staff_id']);
        } else {
            $monthlySalary = $this->applyBranchScope($monthlySalary, $scope);
        }

        $monthlySalary = $monthlySalary
            ->whereYear('payment_date', now()->year)
            ->selectRaw('MONTH(payment_date) as month, SUM(paid_amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        $months = collect(range(1, 12))->map(function ($month) use ($monthlyFeeCollection, $monthlyExpenses, $monthlySalary) {
            return [
                'month'   => Carbon::create()->month($month)->format('M'),
                'fee'     => (float) ($monthlyFeeCollection[$month] ?? 0),
                'expense' => (float) ($monthlyExpenses[$month] ?? 0),
                'salary'  => (float) ($monthlySalary[$month] ?? 0),
            ];
        });

        /*
        |--------------------------------------------------------------------------
        | Dashboard Cards
        |--------------------------------------------------------------------------
        */

        $cards = [
            [
                'title' => 'Branches',
                'value' => $totalBranches,
                'icon'  => 'fas fa-school',
                'color' => 'indigo',
                'show'  => ! $scope['is_student'],
            ],
            [
                'title' => 'Courses',
                'value' => $totalCourses,
                'icon'  => 'fas fa-book',
                'color' => 'blue',
                'show'  => true,
            ],
            [
                'title' => 'Subjects',
                'value' => $totalSubjects,
                'icon'  => 'fas fa-book-open',
                'color' => 'purple',
                'show'  => true,
            ],
            [
                'title' => 'Batches',
                'value' => $totalBatches,
                'icon'  => 'fas fa-layer-group',
                'color' => 'slate',
                'show'  => true,
            ],
            [
                'title' => 'Students',
                'value' => $totalStudents,
                'icon'  => 'fas fa-user-graduate',
                'color' => 'green',
                'show'  => ! $scope['is_student'],
            ],
            [
                'title' => 'Teachers',
                'value' => $totalTeachers,
                'icon'  => 'fas fa-chalkboard-teacher',
                'color' => 'orange',
                'show'  => ! $scope['is_student'],
            ],
            [
                'title' => 'Staff',
                'value' => $totalStaff,
                'icon'  => 'fas fa-user-tie',
                'color' => 'pink',
                'show'  => $scope['is_admin'] || $scope['is_manager'],
            ],
            [
                'title' => 'Enquiries',
                'value' => $totalEnquiries,
                'icon'  => 'fas fa-headset',
                'color' => 'red',
                'show'  => ! $scope['is_student'] && ! $scope['is_teacher'],
            ],
            [
                'title' => 'Exams',
                'value' => $totalExams,
                'icon'  => 'fas fa-clipboard-list',
                'color' => 'indigo',
                'show'  => true,
            ],
            [
                'title' => 'Study Material',
                'value' => $totalStudyMaterials,
                'icon'  => 'fas fa-book-reader',
                'color' => 'blue',
                'show'  => true,
            ],
            [
                'title' => 'Notices',
                'value' => $totalNotices,
                'icon'  => 'fas fa-bullhorn',
                'color' => 'orange',
                'show'  => true,
            ],
        ];

        return view('home', compact(
            'scope',
            'cards',
            'totalUsers',
            'totalFeeCollection',
            'totalFeeDue',
            'totalExpenses',
            'totalSalaryPaid',
            'totalSalaryDue',
            'netBalance',
            'totalEnquiries',
            'newEnquiries',
            'convertedEnquiries',
            'pendingFollowUps',
            'completedExams',
            'upcomingExamsCount',
            'recentFeePayments',
            'recentEnquiries',
            'upcomingExams',
            'recentStudyMaterials',
            'recentNotices',
            'myStudents',
            'months'
        ));
    }

  private function resolveScope($user): array
{
    $roleTitles = $user->roles()
        ->pluck('title')
        ->map(fn ($role) => strtolower(trim($role)))
        ->toArray();

    $isAdmin   = in_array('admin', $roleTitles) || $user->is_admin;
    $isManager = in_array('branch manager', $roleTitles) || in_array('manager', $roleTitles);
    $isTeacher = in_array('teacher', $roleTitles);
    $isStaff   = in_array('staff', $roleTitles);
    $isStudent = in_array('student', $roleTitles);

    $teacher = Teacher::where('user_id', $user->id)->first();
    $staff   = Staff::where('user_id', $user->id)->first();
    $student = Student::where('user_id', $user->id)->first();

    // Branch Manager ke liye branches.manager_id se branch nikalega
    $managerBranch = Branch::where('manager_id', $user->id)->first();

    $branchId = null;

    if ($isAdmin) {
        $branchId = null;
    } elseif ($isManager) {
        $branchId = $managerBranch->id ?? $staff->branch_id ?? null;
    } elseif ($isStaff) {
        $branchId = $staff->branch_id ?? null;
    } elseif ($isTeacher) {
        $branchId = $teacher->branch_id ?? null;
    } elseif ($isStudent) {
        $branchId = $student->branch_id ?? null;
    }

    return [
        'role_label'  => $isAdmin
            ? 'Admin / Owner'
            : ($isManager
                ? 'Branch Manager'
                : ($isTeacher
                    ? 'Teacher'
                    : ($isStudent
                        ? 'Student'
                        : ($isStaff ? 'Staff' : 'User')))),

        'is_admin'    => $isAdmin,
        'is_manager'  => $isManager,
        'is_teacher'  => $isTeacher,
        'is_staff'    => $isStaff,
        'is_student'  => $isStudent,

        'branch_id'   => $branchId,

        'teacher_id'  => $teacher->id ?? null,
        'staff_id'    => $staff->id ?? null,
        'student_id'  => $student->id ?? null,

        'course_id'   => $student->course_id ?? null,
        'batch_id'    => $student->batch_id ?? null,

        // optional debug/use ke liye
        'manager_branch_id' => $managerBranch->id ?? null,
    ];
}

    private function studentIdsForScope(array $scope)
    {
        if ($scope['is_admin']) {
            return null;
        }

        if ($scope['is_student'] && $scope['student_id']) {
            return collect([$scope['student_id']]);
        }

        if ($scope['is_teacher']) {
            if (Schema::hasColumn('batches', 'teacher_id') && $scope['teacher_id']) {
                $batchIds = Batch::where('teacher_id', $scope['teacher_id'])->pluck('id');

                if ($batchIds->count()) {
                    return Student::whereIn('batch_id', $batchIds)->pluck('id');
                }
            }

            if ($scope['branch_id']) {
                return Student::where('branch_id', $scope['branch_id'])->pluck('id');
            }
        }

        if (($scope['is_manager'] || $scope['is_staff']) && $scope['branch_id']) {
            return Student::where('branch_id', $scope['branch_id'])->pluck('id');
        }

        return collect([]);
    }

    private function applyBranchScope(Builder $query, array $scope, string $column = 'branch_id'): Builder
    {
        if ($scope['is_admin']) {
            return $query;
        }

        if ($scope['branch_id']) {
            return $query->where($column, $scope['branch_id']);
        }

        return $query->whereRaw('1 = 0');
    }

    private function applyStudentScope(Builder $query, array $scope, $studentIds): Builder
    {
        if ($scope['is_admin']) {
            return $query;
        }

        if ($scope['is_student'] && $scope['student_id']) {
            return $query->where('id', $scope['student_id']);
        }

        if ($studentIds && $studentIds->count()) {
            return $query->whereIn('id', $studentIds);
        }

        return $query->whereRaw('1 = 0');
    }

    private function applyFinanceScope(Builder $query, array $scope, $studentIds): Builder
    {
        if ($scope['is_admin']) {
            return $query;
        }

        if ($scope['is_student'] && $scope['student_id']) {
            return $query->where('student_id', $scope['student_id']);
        }

        if ($studentIds && $studentIds->count()) {
            return $query->whereIn('student_id', $studentIds);
        }

        if ($scope['branch_id']) {
            return $query->where('branch_id', $scope['branch_id']);
        }

        return $query->whereRaw('1 = 0');
    }

    private function applyAcademicScope(Builder $query, array $scope): Builder
    {
        if ($scope['is_admin']) {
            return $query;
        }

        if ($scope['is_student']) {
            return $query
                ->when($scope['branch_id'], fn ($q) => $q->where(function ($qq) use ($scope) {
                    $qq->whereNull('branch_id')->orWhere('branch_id', $scope['branch_id']);
                }))
                ->when($scope['course_id'], fn ($q) => $q->where(function ($qq) use ($scope) {
                    $qq->whereNull('course_id')->orWhere('course_id', $scope['course_id']);
                }))
                ->when($scope['batch_id'], fn ($q) => $q->where(function ($qq) use ($scope) {
                    $qq->whereNull('batch_id')->orWhere('batch_id', $scope['batch_id']);
                }));
        }

        if ($scope['branch_id']) {
            return $query->where(function ($q) use ($scope) {
                $q->whereNull('branch_id')->orWhere('branch_id', $scope['branch_id']);
            });
        }

        return $query->whereRaw('1 = 0');
    }

    private function applyNoticeScope(Builder $query, array $scope): Builder
    {
        if ($scope['is_admin']) {
            return $query;
        }

        if ($scope['is_student']) {
            return $query->where(function ($q) use ($scope) {
                $q->where('target_audience', 'all')
                    ->orWhere('target_audience', 'students')
                    ->orWhere(function ($qq) use ($scope) {
                        $qq->where('target_audience', 'branch')->where('branch_id', $scope['branch_id']);
                    })
                    ->orWhere(function ($qq) use ($scope) {
                        $qq->where('target_audience', 'course')->where('course_id', $scope['course_id']);
                    })
                    ->orWhere(function ($qq) use ($scope) {
                        $qq->where('target_audience', 'batch')->where('batch_id', $scope['batch_id']);
                    });
            });
        }

        if ($scope['is_teacher']) {
            return $query->where(function ($q) use ($scope) {
                $q->where('target_audience', 'all')
                    ->orWhere('target_audience', 'teachers')
                    ->orWhere(function ($qq) use ($scope) {
                        $qq->where('target_audience', 'branch')->where('branch_id', $scope['branch_id']);
                    });
            });
        }

        if ($scope['is_manager']) {
            return $query->where(function ($q) use ($scope) {
                $q->where('target_audience', 'all')
                    ->orWhere('target_audience', 'managers')
                    ->orWhere(function ($qq) use ($scope) {
                        $qq->where('target_audience', 'branch')->where('branch_id', $scope['branch_id']);
                    });
            });
        }

        if ($scope['is_staff']) {
            return $query->where(function ($q) use ($scope) {
                $q->where('target_audience', 'all')
                    ->orWhere('target_audience', 'staff')
                    ->orWhere(function ($qq) use ($scope) {
                        $qq->where('target_audience', 'branch')->where('branch_id', $scope['branch_id']);
                    });
            });
        }

        return $query->whereRaw('1 = 0');
    }
}