<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Models\SalaryPayment;
use App\Models\Teacher;
use App\Services\SalaryCalculationService;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SalaryReportsController extends Controller
{
    use AppliesErpScope;

    public function index()
    {
        abort_if(Gate::denies('salary_report_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $salaryMonth = request('salary_month', now()->format('Y-m'));
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
        $payments = $payments->latest()->get();

        $teachers = $this->scopeBranchQuery(Teacher::with('user'))->get()->mapWithKeys(fn ($teacher) => [$teacher->id => $teacher->user->name ?? ('Teacher #' . $teacher->id)])->prepend(trans('global.pleaseSelect'), '');

        return view('admin.salaryReports.index', compact('payments', 'salaryMonth', 'teachers'));
    }

    public function calculate(Request $request, SalaryCalculationService $service)
    {
        abort_if(Gate::denies('salary_calculate'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $data = $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'salary_month' => ['required', 'date_format:Y-m'],
        ]);

        $teacher = Teacher::findOrFail($data['teacher_id']);
        $this->assertBranchAccess($teacher);
        $service->calculateAndStoreTeacher($teacher, $data['salary_month']);

        return redirect()->route('admin.salary-reports.index', ['salary_month' => $data['salary_month']])
            ->with('message', 'Teacher salary calculated successfully.');
    }
}
