<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Models\SalaryPayment;
use App\Models\Staff;
use App\Models\Teacher;
use App\Services\SalaryCalculationService;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SalaryReportsController extends Controller
{
    use AppliesErpScope;

    public function index(Request $request)
    {
        abort_if(Gate::denies('salary_report_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $salaryMonth = $request->get('salary_month', now()->format('Y-m'));
        $employeeType = $request->get('employee_type');
        $salaryType = $request->get('salary_type');
        $employeeId = $request->get('employee_id');

        $payments = SalaryPayment::with(['teacher.user', 'staff.user', 'branch'])
            ->where('salary_month', $salaryMonth);

        $scope = $this->erpScope();

        if ($scope['is_teacher'] && $scope['teacher_id']) {
            $payments->where('teacher_id', $scope['teacher_id']);
        } elseif ($scope['is_staff'] && $scope['staff_id']) {
            $payments->where('staff_id', $scope['staff_id']);
        } elseif (! $scope['is_admin']) {
            $this->scopeBranchQuery($payments);
        }

        if ($employeeType) {
            $payments->where('employee_type', $employeeType);
        }

        if ($salaryType) {
            $payments->where('salary_type', $salaryType);
        }

        if ($employeeId) {
            $employeeType === 'staff' ? $payments->where('staff_id', $employeeId) : $payments->where('teacher_id', $employeeId);
        }

        $payments = $payments->latest()->get();

        $teachers = $this->scopeBranchQuery(Teacher::with('user'))
            ->get()
            ->mapWithKeys(fn ($teacher) => [$teacher->id => $teacher->user->name ?? ('Teacher #' . $teacher->id)])
            ->prepend(trans('global.pleaseSelect'), '');

        $staffList = $this->scopeBranchQuery(Staff::with('user'))
            ->get()
            ->mapWithKeys(fn ($member) => [$member->id => $member->user->name ?? ('Staff #' . $member->id)])
            ->prepend(trans('global.pleaseSelect'), '');

        $filters = [
            'salary_month' => $salaryMonth,
            'employee_type' => $employeeType,
            'salary_type' => $salaryType,
            'employee_id' => $employeeId,
        ];

        return view('admin.salaryReports.index', compact('payments', 'salaryMonth', 'teachers', 'staffList', 'filters'));
    }

    public function calculate(Request $request, SalaryCalculationService $service)
    {
        abort_if(Gate::denies('salary_calculate'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validate([
            'employee_type' => ['required', 'in:teacher,staff'],
            'teacher_id' => ['required_if:employee_type,teacher', 'nullable', 'exists:teachers,id'],
            'staff_id' => ['required_if:employee_type,staff', 'nullable', 'exists:staff,id'],
            'salary_month' => ['required', 'date_format:Y-m'],
        ]);

        if ($data['employee_type'] === 'teacher') {
            $teacher = Teacher::findOrFail($data['teacher_id']);
            $this->assertBranchAccess($teacher);
            $service->calculateAndStoreTeacher($teacher, $data['salary_month']);

            $message = 'Teacher salary calculated successfully.';
        } else {
            $staff = Staff::findOrFail($data['staff_id']);
            $this->assertBranchAccess($staff);
            $service->calculateAndStoreStaff($staff, $data['salary_month']);

            $message = 'Staff salary calculated successfully.';
        }

        return redirect()->route('admin.salary-reports.index', ['salary_month' => $data['salary_month']])
            ->with('message', $message);
    }
}
