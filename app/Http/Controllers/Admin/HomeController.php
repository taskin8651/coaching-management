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
use App\Models\Teacher;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $totalBranches = Branch::count();
        $totalCourses  = Course::count();
        $totalBatches  = Batch::count();
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $totalStaff    = Staff::count();

        $totalEnquiries = Enquiry::count();

        $newEnquiries = Enquiry::where('status', 'new')->count();

        $convertedEnquiries = Enquiry::where('status', 'converted')->count();

        $pendingFollowUps = Enquiry::whereNotNull('next_follow_up_date')
            ->whereDate('next_follow_up_date', '<=', $today)
            ->whereNotIn('status', ['converted', 'rejected', 'not_interested'])
            ->count();

        $totalFeeCollection = FeePayment::where('payment_status', '!=', 'cancelled')
            ->sum('paid_amount');

        $totalFeeDue = FeePayment::where('payment_status', '!=', 'cancelled')
            ->sum('due_amount');

        $totalExpenses = Expense::where('status', 'paid')
            ->sum('amount');

        $totalSalaryPaid = SalaryPayment::where('payment_status', '!=', 'cancelled')
            ->sum('paid_amount');

        $totalSalaryDue = SalaryPayment::where('payment_status', '!=', 'cancelled')
            ->sum('due_amount');

        $netBalance = $totalFeeCollection - $totalExpenses - $totalSalaryPaid;

        $upcomingExams = Exam::with(['branch', 'course', 'batch', 'subject'])
            ->where('status', 'scheduled')
            ->whereDate('exam_date', '>=', $today)
            ->orderBy('exam_date', 'asc')
            ->take(5)
            ->get();

        $recentFeePayments = FeePayment::with(['student.user', 'branch', 'course'])
            ->latest()
            ->take(5)
            ->get();

        $recentEnquiries = Enquiry::with(['branch', 'course', 'assignedTo'])
            ->latest()
            ->take(5)
            ->get();

        $recentNotices = Notice::with(['branch', 'createdBy'])
            ->where('status', 'published')
            ->latest()
            ->take(5)
            ->get();

        $branchWiseStudents = Branch::withCount('students')
            ->orderByDesc('students_count')
            ->take(6)
            ->get();

        $courseWiseStudents = Course::withCount('students')
            ->orderByDesc('students_count')
            ->take(6)
            ->get();

        $monthlyFeeCollection = FeePayment::where('payment_status', '!=', 'cancelled')
            ->whereYear('payment_date', now()->year)
            ->selectRaw('MONTH(payment_date) as month, SUM(paid_amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        $monthlyExpenses = Expense::where('status', 'paid')
            ->whereYear('expense_date', now()->year)
            ->selectRaw('MONTH(expense_date) as month, SUM(amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        $monthlySalary = SalaryPayment::where('payment_status', '!=', 'cancelled')
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

        return view('home', compact(
            'totalBranches',
            'totalCourses',
            'totalBatches',
            'totalStudents',
            'totalTeachers',
            'totalStaff',
            'totalEnquiries',
            'newEnquiries',
            'convertedEnquiries',
            'pendingFollowUps',
            'totalFeeCollection',
            'totalFeeDue',
            'totalExpenses',
            'totalSalaryPaid',
            'totalSalaryDue',
            'netBalance',
            'upcomingExams',
            'recentFeePayments',
            'recentEnquiries',
            'recentNotices',
            'branchWiseStudents',
            'courseWiseStudents',
            'months'
        ));
    }
}