<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Requests\ApplyLateFeeRequest;
use App\Http\Requests\StoreFeeInstallmentRequest;
use App\Http\Requests\UpdateFeeInstallmentRequest;
use App\Models\FeeAccount;
use App\Models\FeeInstallment;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Services\WhatsappService;
use Gate;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class FeeInstallmentsController extends Controller
{
    use AppliesErpScope;

    public function index()
    {
        abort_if(Gate::denies('fee_installment_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $installments = FeeInstallment::with(['student.user', 'feeStructure']);
        $scope = $this->erpScope();

        if ($scope['is_student'] && $scope['student_id']) {
            $installments->where('student_id', $scope['student_id']);
        } elseif ($scope['is_parent'] && $scope['parent_student_ids']->isNotEmpty()) {
            $installments->whereIn('student_id', $scope['parent_student_ids']);
        } elseif (! $scope['is_admin']) {
            $installments->whereHas('student', fn ($q) => $this->scopeStudentQuery($q));
        }

        $installments = $installments->latest()->get();

        return view('admin.feeInstallments.index', compact('installments'));
    }

    public function create()
    {
        abort_if(Gate::denies('fee_installment_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.feeInstallments.create', $this->formData());
    }

    public function store(StoreFeeInstallmentRequest $request)
    {
        $data = $this->prepare($request->validated());

        FeeInstallment::create($data);

        return redirect()->route('admin.fee-installments.index')->with('message', 'Fee installment saved successfully.');
    }

    public function edit(FeeInstallment $feeInstallment)
    {
        abort_if(Gate::denies('fee_installment_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($feeInstallment);

        return view('admin.feeInstallments.edit', $this->formData() + compact('feeInstallment'));
    }

    public function update(UpdateFeeInstallmentRequest $request, FeeInstallment $feeInstallment)
    {
        $this->checkAccess($feeInstallment);

        $data = $this->prepare($request->validated());

        $feeInstallment->update($data);

        return redirect()->route('admin.fee-installments.index')->with('message', 'Fee installment updated successfully.');
    }

    public function destroy(FeeInstallment $feeInstallment)
    {
        abort_if(Gate::denies('fee_installment_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($feeInstallment);

        abort_if(
            $feeInstallment->payments()->exists(),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'Cannot delete an installment with recorded payments. Remove the linked payments first.'
        );

        $feeInstallment->delete();

        return back()->with('message', 'Fee installment deleted successfully.');
    }

    public function remind(FeeInstallment $feeInstallment, WhatsappService $whatsapp)
    {
        abort_if(Gate::denies('fee_installment_remind'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkAccess($feeInstallment);

        $log = $whatsapp->sendStudentGuardianMessage(
            $feeInstallment->student,
            'fee_due',
            'Fee installment due: ' . $feeInstallment->title . ' amount ' . $feeInstallment->due_amount . ' due on ' . optional($feeInstallment->due_date)->format('d M Y')
        );

        if ($log->status !== 'sent') {
            return back()->with('message', 'Fee reminder could not be sent: ' . ($log->response ?: 'unknown error') . '.');
        }

        $feeInstallment->update(['reminded_at' => now()]);

        return back()->with('message', 'Fee reminder sent successfully.');
    }

    public function applyLateFee(ApplyLateFeeRequest $request, FeeInstallment $feeInstallment)
    {
        $this->checkAccess($feeInstallment);

        $increase = round((float) ($request->validated()['amount'] ?? $feeInstallment->calculateSuggestedLateFee()), 2);

        abort_if($increase <= 0, Response::HTTP_UNPROCESSABLE_ENTITY, 'No late fee applicable for this installment.');

        DB::transaction(function () use ($feeInstallment, $increase) {
            $feeInstallment->update([
                'amount' => $feeInstallment->amount + $increase,
                'due_amount' => $feeInstallment->due_amount + $increase,
                'late_fee_applied_amount' => $feeInstallment->late_fee_applied_amount + $increase,
                'late_fee_applied_at' => now(),
                'late_fee_applied_by_id' => auth()->id(),
            ]);

            if ($feeInstallment->ledger) {
                $feeInstallment->ledger->update(['net_payable' => $feeInstallment->ledger->net_payable + $increase]);
                $feeInstallment->ledger->recalculate();
            }
        });

        return back()->with('message', 'Late fee of ₹' . number_format($increase, 2) . ' applied successfully.');
    }

    private function checkAccess(FeeInstallment $feeInstallment): void
    {
        $scope = $this->erpScope();

        if ($scope['is_admin']) {
            return;
        }

        abort_if(
            ! $this->scopeStudentQuery(Student::query())->where('id', $feeInstallment->student_id)->exists(),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden'
        );
    }

    private function formData(): array
    {
        return [
            'students' => $this->scopeStudentQuery(Student::with('user'))
                ->get()
                ->mapWithKeys(fn ($s) => [$s->id => $s->user->name ?? $s->student_code ?? 'Student #' . $s->id])
                ->prepend(trans('global.pleaseSelect'), ''),
            'feeStructures' => FeeStructure::pluck('title', 'id')->prepend('Optional', ''),
            'feeAccounts' => FeeAccount::where('status', 'active')->pluck('name', 'id')->prepend('Optional', ''),
        ];
    }

    private function prepare(array $data): array
    {
        $data['paid_amount'] = (float) ($data['paid_amount'] ?? 0);
        $data['due_amount'] = max(((float) $data['amount']) - $data['paid_amount'], 0);

        if ($data['due_amount'] <= 0) {
            $data['status'] = 'paid';
        } elseif ($data['paid_amount'] > 0) {
            $data['status'] = 'partial';
        }

        return $data;
    }
}
