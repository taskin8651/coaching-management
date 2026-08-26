<?php

namespace App\Models;

use App\Traits\Auditable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class EventEnrollment extends Model
{
    use Auditable;

    public $table = 'event_enrollments';

    protected $fillable = [
        'event_id',
        'branch_id',
        'student_id',
        'external_contact_id',
        'participant_type',
        'group_size',
        'fee_rule_label',
        'fee_amount',
        'paid_amount',
        'due_amount',
        'payment_status',
        'enrollment_date',
        'status',
        'cancelled_at',
        'cancelled_by_id',
        'cancel_reason',
        'is_present',
        'attendance_marked_at',
        'attendance_marked_by_id',
        'certificate_number',
        'certificate_status',
        'enrolled_by_id',
        'remarks',
    ];

    protected $casts = [
        'fee_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'enrollment_date' => 'date',
        'cancelled_at' => 'datetime',
        'is_present' => 'boolean',
        'attendance_marked_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function externalContact()
    {
        return $this->belongsTo(ExternalContact::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by_id');
    }

    public function enrolledBy()
    {
        return $this->belongsTo(User::class, 'enrolled_by_id');
    }

    public function attendanceMarkedBy()
    {
        return $this->belongsTo(User::class, 'attendance_marked_by_id');
    }

    public function feePayments()
    {
        return $this->hasMany(FeePayment::class, 'event_enrollment_id');
    }

    public function participantName(): string
    {
        if ($this->participant_type === 'student') {
            return $this->student->user->name ?? ('Student #' . $this->student_id);
        }

        return $this->externalContact->name ?? 'External Participant';
    }

    /**
     * Re-sums ALL linked, non-cancelled FeePayment rows (not an incremental add/subtract) —
     * same "resum not delta" philosophy as FeeInstallment::recalculateFromPayments(). Capped at
     * this enrollment's own fee_amount so an accidental overpayment can't inflate paid_amount
     * past what's owed (unlike fee installments, event payments don't feed a credit/advance
     * pool this phase — kept simple deliberately). No-ops for complimentary enrollments, which
     * never carry real payments.
     */
    public function recalculateFromPayments(): void
    {
        if ($this->payment_status === 'complimentary') {
            return;
        }

        $paid = (float) $this->feePayments()->where('payment_status', '!=', 'cancelled')->sum('paid_amount');
        $paidAmount = min(max($paid, 0), (float) $this->fee_amount);
        $dueAmount = max((float) $this->fee_amount - $paidAmount, 0);

        $status = 'unpaid';

        if ($dueAmount <= 0 && $paidAmount > 0) {
            $status = 'paid';
        } elseif ($paidAmount > 0) {
            $status = 'partial';
        }

        $this->update([
            'paid_amount' => round($paidAmount, 2),
            'due_amount' => round($dueAmount, 2),
            'payment_status' => $status,
        ]);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
