<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentFeeLedger;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class StudentFeeLedgersController extends Controller
{
    use AppliesErpScope;

    public function index()
    {
        abort_if(Gate::denies('student_fee_ledger_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ledgers = StudentFeeLedger::with(['student.user', 'feeStructure'])
            ->whereHas('student', fn ($q) => $this->scopeStudentQuery($q))
            ->latest()
            ->get();

        return view('admin.studentFeeLedgers.index', compact('ledgers'));
    }

    public function show(StudentFeeLedger $studentFeeLedger)
    {
        abort_if(Gate::denies('student_fee_ledger_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $scope = $this->erpScope();

        abort_if(
            ! $scope['is_admin'] && ! $this->scopeStudentQuery(Student::query())->where('id', $studentFeeLedger->student_id)->exists(),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden'
        );

        $studentFeeLedger->load([
            'student.user',
            'feeStructure.items.feeHead',
            'assignedBy',
            'concessions.approvedBy',
            'installments.items.feeHead',
            'installments.feeAccount',
            'installments.payments.feeAccount',
            'credits.feeInstallment',
            'credits.feePayment',
            'refunds.feeAccount',
            'refunds.feePayment',
        ]);

        return view('admin.studentFeeLedgers.show', compact('studentFeeLedger'));
    }
}
