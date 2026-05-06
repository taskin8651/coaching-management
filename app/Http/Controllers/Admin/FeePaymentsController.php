<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeePaymentRequest;
use App\Http\Requests\UpdateFeePaymentRequest;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Course;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\User;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FeePaymentsController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('fee_payment_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $feePayments = FeePayment::with(['branch', 'student.user', 'course', 'batch', 'collectedBy'])
            ->latest()
            ->get();

        return view('admin.feePayments.index', compact('feePayments'));
    }

    public function create()
    {
        abort_if(Gate::denies('fee_payment_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branches = Branch::where('status', 'active')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $students = Student::with('user')->get()->pluck('user.name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $courses = Course::where('status', 'active')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $batches = Batch::where('status', 'active')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $paymentModes = $this->paymentModes();

        return view('admin.feePayments.create', compact('branches', 'students', 'courses', 'batches', 'users', 'paymentModes'));
    }

    public function store(StoreFeePaymentRequest $request)
    {
        $data = $this->preparePaymentData($request->validated());

        FeePayment::create($data);

        return redirect()->route('admin.fee-payments.index')->with('message', 'Fee payment created successfully.');
    }

    public function show(FeePayment $feePayment)
    {
        abort_if(Gate::denies('fee_payment_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $feePayment->load(['branch', 'student.user', 'course', 'batch', 'collectedBy']);

        return view('admin.feePayments.show', compact('feePayment'));
    }

    public function edit(FeePayment $feePayment)
    {
        abort_if(Gate::denies('fee_payment_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $branches = Branch::where('status', 'active')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $students = Student::with('user')->get()->pluck('user.name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $courses = Course::where('status', 'active')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $batches = Batch::where('status', 'active')->pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');
        $users = User::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $paymentModes = $this->paymentModes();

        $feePayment->load(['branch', 'student.user', 'course', 'batch', 'collectedBy']);

        return view('admin.feePayments.edit', compact('feePayment', 'branches', 'students', 'courses', 'batches', 'users', 'paymentModes'));
    }

    public function update(UpdateFeePaymentRequest $request, FeePayment $feePayment)
    {
        $data = $this->preparePaymentData($request->validated(), $feePayment);

        $feePayment->update($data);

        return redirect()->route('admin.fee-payments.index')->with('message', 'Fee payment updated successfully.');
    }

    public function destroy(FeePayment $feePayment)
    {
        abort_if(Gate::denies('fee_payment_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $feePayment->delete();

        return back()->with('message', 'Fee payment deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        abort_if(Gate::denies('fee_payment_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        FeePayment::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    private function preparePaymentData(array $data, FeePayment $feePayment = null): array
    {
        $totalFee = (float) ($data['total_fee'] ?? 0);
        $discount = (float) ($data['discount'] ?? 0);
        $paidAmount = (float) ($data['paid_amount'] ?? 0);

        $payableAmount = max($totalFee - $discount, 0);
        $dueAmount = max($payableAmount - $paidAmount, 0);

        if (empty($data['receipt_no'])) {
            $data['receipt_no'] = $feePayment->receipt_no ?? $this->generateReceiptNo();
        }

        $data['discount'] = $discount;
        $data['payable_amount'] = $payableAmount;
        $data['due_amount'] = $dueAmount;

        if (($data['payment_status'] ?? null) !== 'cancelled') {
            if ($paidAmount >= $payableAmount && $payableAmount > 0) {
                $data['payment_status'] = 'paid';
            } elseif ($paidAmount > 0) {
                $data['payment_status'] = 'partial';
            } else {
                $data['payment_status'] = 'due';
            }
        }

        if (empty($data['collected_by_id'])) {
            $data['collected_by_id'] = auth()->id();
        }

        if (empty($data['payment_date'])) {
            $data['payment_date'] = now()->format('Y-m-d');
        }

        return $data;
    }

    private function generateReceiptNo(): string
    {
        $lastPayment = FeePayment::latest('id')->first();
        $nextId = $lastPayment ? $lastPayment->id + 1 : 1;

        return 'REC-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }

    private function paymentModes(): array
    {
        return [
            'cash' => 'Cash',
            'upi' => 'UPI',
            'bank_transfer' => 'Bank Transfer',
            'cheque' => 'Cheque',
            'card' => 'Card',
            'other' => 'Other',
        ];
    }

    public function invoice(FeePayment $feePayment)
{
    abort_if(Gate::denies('fee_payment_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

    $feePayment->load(['branch', 'student.user', 'course', 'batch', 'collectedBy']);

    return view('admin.feePayments.invoice', compact('feePayment'));
}
}