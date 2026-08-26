<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AppliesErpScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\CancelEventEnrollmentRequest;
use App\Http\Requests\StoreEventEnrollmentRequest;
use App\Http\Requests\StoreEventPaymentRequest;
use App\Models\Event;
use App\Models\EventEnrollment;
use App\Models\EventFeeRule;
use App\Models\ExternalContact;
use App\Models\FeePayment;
use App\Models\Student;
use App\Services\ReceiptNumberService;
use Gate;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EventEnrollmentsController extends Controller
{
    use AppliesErpScope;

    public function store(StoreEventEnrollmentRequest $request, Event $event)
    {
        $this->checkEventAccess($event);

        abort_if(! $event->canEnroll(), Response::HTTP_UNPROCESSABLE_ENTITY, 'This event is not open for enrollment.');

        $data = $request->validated();

        if ($event->capacity && $event->enrollments()->where('status', '!=', 'cancelled')->count() >= $event->capacity) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'This event has reached capacity.');
        }

        $enrollment = DB::transaction(function () use ($event, $data) {
            if ($data['participant_type'] === 'student') {
                $student = $this->scopeStudentQuery(Student::query())->find($data['student_id']);

                abort_if(! $student, Response::HTTP_FORBIDDEN, 'Invalid student.');

                abort_if(
                    EventEnrollment::where('event_id', $event->id)->where('student_id', $student->id)->where('status', '!=', 'cancelled')->exists(),
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    'This student is already enrolled in this event.'
                );

                $resolved = EventFeeRule::resolveFor($event, 'student', 1, now());

                return EventEnrollment::create([
                    'event_id' => $event->id,
                    'branch_id' => $student->branch_id,
                    'student_id' => $student->id,
                    'participant_type' => 'student',
                    'group_size' => 1,
                    'fee_rule_label' => $resolved['label'],
                    'fee_amount' => $resolved['amount'],
                    'due_amount' => $resolved['amount'],
                    'payment_status' => 'unpaid',
                    'enrollment_date' => now()->format('Y-m-d'),
                    'status' => 'registered',
                    'enrolled_by_id' => auth()->id(),
                    'remarks' => $data['remarks'] ?? null,
                ]);
            }

            abort_if(! $event->external_enrollment_allowed, Response::HTTP_FORBIDDEN, 'External enrollment is not allowed for this event.');

            $contact = ! empty($data['external_contact_id'])
                ? ExternalContact::find($data['external_contact_id'])
                : ExternalContact::create(array_merge($data['new_contact'] ?? [], ['created_by_id' => auth()->id()]));

            abort_if(! $contact, Response::HTTP_UNPROCESSABLE_ENTITY, 'Invalid external contact.');

            abort_if(
                EventEnrollment::where('event_id', $event->id)->where('external_contact_id', $contact->id)->where('status', '!=', 'cancelled')->exists(),
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'This contact is already enrolled in this event.'
            );

            $resolved = EventFeeRule::resolveFor($event, 'external', 1, now());

            return EventEnrollment::create([
                'event_id' => $event->id,
                'branch_id' => $event->branch_id,
                'external_contact_id' => $contact->id,
                'participant_type' => 'external',
                'group_size' => 1,
                'fee_rule_label' => $resolved['label'],
                'fee_amount' => $resolved['amount'],
                'due_amount' => $resolved['amount'],
                'payment_status' => 'unpaid',
                'enrollment_date' => now()->format('Y-m-d'),
                'status' => 'registered',
                'enrolled_by_id' => auth()->id(),
                'remarks' => $data['remarks'] ?? null,
            ]);
        });

        return redirect()->route('admin.events.show', $event)->with('message', 'Enrolled ' . $enrollment->participantName() . ' successfully.');
    }

    public function cancel(CancelEventEnrollmentRequest $request, EventEnrollment $eventEnrollment)
    {
        $this->checkEnrollmentAccess($eventEnrollment);

        abort_if($eventEnrollment->status === 'cancelled', Response::HTTP_UNPROCESSABLE_ENTITY, 'This enrollment is already cancelled.');

        $eventEnrollment->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by_id' => auth()->id(),
            'cancel_reason' => $request->validated()['cancel_reason'],
        ]);

        return back()->with('message', 'Enrollment cancelled. Any collected payments must be cancelled separately if a refund is needed.');
    }

    public function markAttendance(EventEnrollment $eventEnrollment)
    {
        abort_if(Gate::denies('event_attendance_mark'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkEnrollmentAccess($eventEnrollment);

        $present = filter_var(request('present', true), FILTER_VALIDATE_BOOLEAN);

        $eventEnrollment->update([
            'is_present' => $present,
            'attendance_marked_at' => now(),
            'attendance_marked_by_id' => auth()->id(),
        ]);

        return back()->with('message', 'Attendance marked as ' . ($present ? 'Present' : 'Absent') . '.');
    }

    public function markCertificate(EventEnrollment $eventEnrollment)
    {
        abort_if(Gate::denies('event_certificate_mark'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkEnrollmentAccess($eventEnrollment);

        $data = request()->validate([
            'certificate_number' => ['nullable', 'string', 'max:100'],
        ]);

        $eventEnrollment->update([
            'certificate_number' => $data['certificate_number'] ?? $eventEnrollment->certificate_number,
            'certificate_status' => 'issued',
        ]);

        return back()->with('message', 'Certificate marked as issued.');
    }

    public function markComplimentary(EventEnrollment $eventEnrollment)
    {
        abort_if(Gate::denies('event_fee_rule_manage'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $this->checkEnrollmentAccess($eventEnrollment);

        $eventEnrollment->update([
            'fee_amount' => 0,
            'paid_amount' => 0,
            'due_amount' => 0,
            'payment_status' => 'complimentary',
            'fee_rule_label' => 'Complimentary',
        ]);

        return back()->with('message', 'Enrollment marked complimentary.');
    }

    public function collectPayment(StoreEventPaymentRequest $request, EventEnrollment $eventEnrollment, ReceiptNumberService $receiptNumbers)
    {
        $this->checkEnrollmentAccess($eventEnrollment);

        abort_if($eventEnrollment->payment_status === 'complimentary', Response::HTTP_UNPROCESSABLE_ENTITY, 'This enrollment is complimentary and does not accept payments.');
        abort_if($eventEnrollment->status === 'cancelled', Response::HTTP_UNPROCESSABLE_ENTITY, 'This enrollment is cancelled.');

        $data = $request->validated();

        $paidAmount = (float) $data['paid_amount'];
        $dueAmount = max((float) $eventEnrollment->fee_amount - (float) $eventEnrollment->paid_amount - $paidAmount, 0);

        $paymentDate = ! empty($data['payment_date']) ? \Carbon\Carbon::parse($data['payment_date']) : now();
        $academicYear = $receiptNumbers->academicYearFor($paymentDate);
        $receipt = $receiptNumbers->next($eventEnrollment->branch_id, $academicYear);

        DB::transaction(function () use ($eventEnrollment, $data, $paidAmount, $dueAmount, $receipt, $paymentDate) {
            FeePayment::create([
                'branch_id' => $eventEnrollment->branch_id,
                'student_id' => $eventEnrollment->participant_type === 'student' ? $eventEnrollment->student_id : null,
                'event_enrollment_id' => $eventEnrollment->id,
                'fee_account_id' => $data['fee_account_id'],
                'collected_by_id' => auth()->id(),
                'receipt_no' => $receipt['receipt_no'],
                'receipt_academic_year' => $receipt['academic_year'],
                'receipt_sequence_no' => $receipt['sequence_no'],
                'total_fee' => $eventEnrollment->fee_amount,
                'discount' => 0,
                'payable_amount' => $eventEnrollment->fee_amount,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'gst_applicable' => (bool) ($data['gst_applicable'] ?? false),
                'gst_percent' => (bool) ($data['gst_applicable'] ?? false) ? ($data['gst_percent'] ?? 0) : 0,
                'gst_amount' => (bool) ($data['gst_applicable'] ?? false) ? ($data['gst_amount'] ?? 0) : 0,
                'payment_mode' => $data['payment_mode'],
                'cheque_number' => $data['cheque_number'] ?? null,
                'cheque_date' => $data['cheque_date'] ?? null,
                'cheque_bank_name' => $data['cheque_bank_name'] ?? null,
                'upi_txn_ref' => $data['upi_txn_ref'] ?? null,
                'neft_rtgs_imps_utr' => $data['neft_rtgs_imps_utr'] ?? null,
                'neft_rtgs_imps_bank_name' => $data['neft_rtgs_imps_bank_name'] ?? null,
                'card_gateway_ref' => $data['card_gateway_ref'] ?? null,
                'other_reference' => $data['other_reference'] ?? null,
                'payment_date' => $paymentDate->format('Y-m-d'),
                'payment_status' => $dueAmount <= 0 ? 'paid' : 'partial',
                'remarks' => $data['remarks'] ?? null,
            ]);

            $eventEnrollment->recalculateFromPayments();
        });

        return back()->with('message', 'Payment collected successfully.');
    }

    private function checkEventAccess(Event $event): void
    {
        $scope = $this->erpScope();

        if ($scope['is_admin']) {
            return;
        }

        abort_if(
            $event->branch_id && $event->branch_id != $scope['branch_id'],
            Response::HTTP_FORBIDDEN,
            '403 Forbidden'
        );
    }

    private function checkEnrollmentAccess(EventEnrollment $eventEnrollment): void
    {
        $scope = $this->erpScope();

        if ($scope['is_admin']) {
            return;
        }

        if ($eventEnrollment->participant_type === 'student') {
            abort_if(
                ! $this->scopeStudentQuery(Student::query())->where('id', $eventEnrollment->student_id)->exists(),
                Response::HTTP_FORBIDDEN,
                '403 Forbidden'
            );

            return;
        }

        abort_if(
            $eventEnrollment->branch_id && $eventEnrollment->branch_id != $scope['branch_id'],
            Response::HTTP_FORBIDDEN,
            '403 Forbidden'
        );
    }
}
