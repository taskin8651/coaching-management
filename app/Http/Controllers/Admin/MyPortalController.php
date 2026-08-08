<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Models\FacultyLogBook;
use App\Models\FeeInstallment;
use App\Models\Homework;
use App\Models\Notice;
use App\Models\ReportCard;
use App\Models\SalaryPayment;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentRemark;
use App\Models\StudyMaterial;
use App\Models\TeacherAssignment;
use App\Models\Timetable;
use Carbon\Carbon;
use Gate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class MyPortalController extends Controller
{
    use AppliesErpScope;

    public function index()
    {
        abort_if(Gate::denies('my_portal_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $scope = $this->erpScope();
        $studentIds = $this->visibleStudentIds($scope);
        $portalStudent = $studentIds->isNotEmpty()
            ? Student::with(['user', 'branch', 'course', 'batch.subjects', 'studentBatches.batch', 'studentBatches.subject'])
                ->find($studentIds->first())
            : null;

        $visibleBatchIds = $this->visibleBatchIds($scope, $studentIds);
        $studentBatchIds = $visibleBatchIds->filter()->unique()->values();
        $studentSubjectIds = $this->visibleSubjectIds($portalStudent, $studentBatchIds);
        $today = Carbon::today('Asia/Kolkata');
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();

        $studentAttendances = StudentAttendance::with(['student.user', 'batch', 'subject'])
            ->when($studentIds->isNotEmpty(), fn ($q) => $q->whereIn('student_id', $studentIds), fn ($q) => $q->whereRaw('1 = 0'))
            ->latest('attendance_date')
            ->take(20)
            ->get();

        $homeworks = Homework::with(['batch', 'subject', 'teacher.user', 'submissions'])
            ->when($studentIds->isNotEmpty(), function ($q) use ($studentIds, $studentBatchIds) {
                $q->where('approval_status', 'approved')->where(function ($qq) use ($studentIds, $studentBatchIds) {
                    $qq->whereHas('submissions', fn ($sub) => $sub->whereIn('student_id', $studentIds));

                    if ($studentBatchIds->isNotEmpty()) {
                        $qq->orWhereIn('batch_id', $studentBatchIds);
                    }
                });
            })
            ->when($scope['is_teacher'] && $scope['teacher_id'], fn ($q) => $q->orWhere('teacher_id', $scope['teacher_id']))
            ->when(! $scope['is_admin'] && ! $scope['is_teacher'] && $studentIds->isEmpty(), fn ($q) => $q->whereRaw('1 = 0'))
            ->latest()
            ->take(20)
            ->get();

        $feeInstallments = FeeInstallment::with(['student.user'])
            ->when($studentIds->isNotEmpty(), fn ($q) => $q->whereIn('student_id', $studentIds), fn ($q) => $q->whereRaw('1 = 0'))
            ->orderBy('due_date')
            ->take(20)
            ->get();

        $remarks = StudentRemark::with(['student.user', 'teacher.user'])
            ->when($studentIds->isNotEmpty(), fn ($q) => $q->whereIn('student_id', $studentIds)->where('visible_to_parent', true)->where('approval_status', 'approved'), fn ($q) => $q->whereRaw('1 = 0'))
            ->latest('remark_date')
            ->take(20)
            ->get();

        $reportCards = ReportCard::with(['student.user', 'exam', 'batch'])
            ->when($studentIds->isNotEmpty(), fn ($q) => $q->whereIn('student_id', $studentIds)->where('published_to_parent', true), fn ($q) => $q->whereRaw('1 = 0'))
            ->latest('published_at')
            ->latest()
            ->take(20)
            ->get();

        $studyMaterials = StudyMaterial::with(['batch', 'subject'])
            ->when($studentIds->isNotEmpty(), function ($q) use ($scope, $visibleBatchIds, $studentSubjectIds) {
                $q->where(function ($qq) use ($scope) {
                    $qq->whereNull('branch_id')->orWhere('branch_id', $scope['branch_id']);
                })->where(function ($qq) use ($visibleBatchIds, $studentSubjectIds) {
                    $qq->where(function ($general) use ($studentSubjectIds) {
                        $general->whereNull('batch_id');

                        if ($studentSubjectIds->isNotEmpty()) {
                            $general->where(fn ($subj) => $subj->whereNull('subject_id')->orWhereIn('subject_id', $studentSubjectIds));
                        } else {
                            $general->whereNull('subject_id');
                        }
                    });

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

        $recentNotices = Notice::query()
            ->when($portalStudent, function ($q) use ($portalStudent, $studentBatchIds) {
                $q->where('status', 'published')
                    ->where(function ($qq) use ($portalStudent, $studentBatchIds) {
                        $qq->where('target_audience', 'all')
                            ->orWhere('target_audience', 'students')
                            ->orWhere(fn ($branch) => $branch->where('target_audience', 'branch')->where('branch_id', $portalStudent->branch_id))
                            ->orWhere(fn ($course) => $course->where('target_audience', 'course')->where('course_id', $portalStudent->course_id));

                        if ($studentBatchIds->isNotEmpty()) {
                            $qq->orWhere(fn ($batch) => $batch->where('target_audience', 'batch')->whereIn('batch_id', $studentBatchIds));
                        }
                    });
            }, fn ($q) => $q->whereRaw('1 = 0'))
            ->latest('publish_date')
            ->take(4)
            ->get();

        $attendanceThisMonth = StudentAttendance::query()
            ->when($studentIds->isNotEmpty(), fn ($q) => $q->whereIn('student_id', $studentIds), fn ($q) => $q->whereRaw('1 = 0'))
            ->whereBetween('attendance_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get();

        $attendanceTotal = $attendanceThisMonth->count();
        $attendancePresent = $attendanceThisMonth->whereIn('status', ['present', 'late'])->count();
        $attendancePercent = $attendanceTotal > 0 ? round(($attendancePresent / $attendanceTotal) * 100) : 0;

        $homeworkPending = $homeworks->filter(function ($homework) use ($portalStudent) {
            if (! $portalStudent) {
                return false;
            }

            $submission = $homework->submissions->firstWhere('student_id', $portalStudent->id);

            return ! $submission || in_array($submission->status, ['pending', 'incomplete'], true);
        })->count();

        $feeTotal = $feeInstallments->sum('amount');
        $feePaid = $feeInstallments->sum('paid_amount');
        $feeDue = $feeInstallments->sum('due_amount');

        $upcomingExams = \App\Models\Exam::query()
            ->when($studentBatchIds->isNotEmpty(), fn ($q) => $q->whereIn('batch_id', $studentBatchIds), fn ($q) => $q->whereRaw('1 = 0'))
            ->whereDate('exam_date', '>=', $today->toDateString())
            ->where('status', '!=', 'cancelled')
            ->count();

        $teacherCount = TeacherAssignment::query()
            ->when($studentBatchIds->isNotEmpty(), fn ($q) => $q->whereIn('batch_id', $studentBatchIds), fn ($q) => $q->whereRaw('1 = 0'))
            ->where('status', 'active')
            ->distinct('teacher_id')
            ->count('teacher_id');

        $classesPerWeek = Timetable::query()
            ->when($studentBatchIds->isNotEmpty(), fn ($q) => $q->whereIn('batch_id', $studentBatchIds), fn ($q) => $q->whereRaw('1 = 0'))
            ->where('status', '!=', 'cancelled')
            ->count();

        $timetables = Timetable::with(['subject', 'batch'])
            ->when($studentBatchIds->isNotEmpty(), fn ($q) => $q->whereIn('batch_id', $studentBatchIds), fn ($q) => $q->whereRaw('1 = 0'))
            ->where('status', '!=', 'cancelled')
            ->orderBy('start_time')
            ->get();

        $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $timeSlots = $timetables->map(fn ($row) => substr((string) $row->start_time, 0, 5) . ' - ' . substr((string) $row->end_time, 0, 5))
            ->unique()
            ->values();
        $timetableRows = $timeSlots->map(function ($slot) use ($weekDays, $timetables) {
            [$start, $end] = array_map('trim', explode('-', $slot));
            $days = collect($weekDays)->mapWithKeys(function ($day) use ($timetables, $start, $end) {
                $class = $timetables->first(function ($row) use ($day, $start, $end) {
                    return strcasecmp((string) $row->day_of_week, $day) === 0
                        && substr((string) $row->start_time, 0, 5) === $start
                        && substr((string) $row->end_time, 0, 5) === $end;
                });

                return [$day => $class];
            });

            return ['slot' => $slot, 'days' => $days];
        });

        $portalStats = [
            ['label' => 'Attendance', 'value' => $attendancePercent . '%', 'note' => 'This Month', 'icon' => 'fa-calendar-check', 'color' => 'blue'],
            ['label' => 'Homework', 'value' => $homeworkPending, 'note' => 'Pending', 'icon' => 'fa-book-open', 'color' => 'green'],
            ['label' => 'Fees Due', 'value' => '₹' . number_format($feeDue, 0), 'note' => 'Pending Amount', 'icon' => 'fa-rupee-sign', 'color' => 'purple'],
            ['label' => 'Exams', 'value' => $upcomingExams, 'note' => 'Upcoming', 'icon' => 'fa-clipboard-list', 'color' => 'orange'],
            ['label' => 'Remarks', 'value' => $remarks->count(), 'note' => 'New', 'icon' => 'fa-comment-dots', 'color' => 'cyan'],
        ];

        $teacherTimetables = collect();
        $facultyLogs = collect();
        $salaryPayments = collect();
        $teacherStats = [];

        if ($scope['is_teacher'] && $scope['teacher_id']) {
            $teacherTimetables = Timetable::with(['batch', 'subject'])
                ->where('teacher_id', $scope['teacher_id'])
                ->where('status', '!=', 'cancelled')
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get();

            $facultyLogs = FacultyLogBook::with(['batch', 'subject'])
                ->where('teacher_id', $scope['teacher_id'])
                ->latest('lecture_date')
                ->take(20)
                ->get();

            $salaryPayments = SalaryPayment::where('teacher_id', $scope['teacher_id'])
                ->latest('payment_date')
                ->take(12)
                ->get();

            $pendingApprovalLogs = FacultyLogBook::where('teacher_id', $scope['teacher_id'])
                ->where('approval_status', 'pending')
                ->count();

            $latestSalary = $salaryPayments->first();

            $teacherStats = [
                ['label' => 'Weekly Classes', 'value' => $teacherTimetables->count(), 'note' => 'Scheduled', 'icon' => 'fa-calendar-check', 'color' => 'blue'],
                ['label' => 'Faculty Logs', 'value' => $facultyLogs->count(), 'note' => 'Recent', 'icon' => 'fa-clipboard-list', 'color' => 'green'],
                ['label' => 'Pending Approval', 'value' => $pendingApprovalLogs, 'note' => 'Logs', 'icon' => 'fa-hourglass-half', 'color' => 'orange'],
                ['label' => 'Last Salary', 'value' => $latestSalary ? '₹' . number_format($latestSalary->net_salary, 0) : '-', 'note' => $latestSalary ? $latestSalary->salary_month : 'No record', 'icon' => 'fa-rupee-sign', 'color' => 'purple'],
            ];
        }

        return view('admin.myPortal.index', compact(
            'scope',
            'portalStudent',
            'studentBatchIds',
            'studentSubjectIds',
            'portalStats',
            'attendancePercent',
            'feeTotal',
            'feePaid',
            'feeDue',
            'teacherCount',
            'classesPerWeek',
            'recentNotices',
            'weekDays',
            'timetableRows',
            'studentAttendances',
            'homeworks',
            'feeInstallments',
            'remarks',
            'reportCards',
            'studyMaterials',
            'teacherTimetables',
            'facultyLogs',
            'salaryPayments',
            'teacherStats'
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

    private function visibleSubjectIds(?Student $student, Collection $batchIds): Collection
    {
        if (! $student) {
            return collect();
        }

        $batchSubjects = $student->batch && $student->batch->relationLoaded('subjects')
            ? $student->batch->subjects->pluck('id')
            : collect();

        $assignedSubjects = $student->studentBatches
            ->where('status', 'active')
            ->pluck('subject_id');

        $pivotSubjects = $batchIds->isNotEmpty()
            ? DB::table('batch_subject')->whereIn('batch_id', $batchIds)->pluck('subject_id')
            : collect();

        return $batchSubjects
            ->merge($assignedSubjects)
            ->merge($pivotSubjects)
            ->filter()
            ->unique()
            ->values();
    }
}
