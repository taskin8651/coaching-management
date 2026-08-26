<?php

namespace App\Models;

use App\Traits\Auditable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeePayment extends Model
{
    use HasFactory, Auditable;

    public $table = 'fee_payments';

    protected $dates = [
        'payment_date',
        'cheque_date',
        'cancelled_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'cheque_date' => 'date',
        'cancelled_at' => 'datetime',
        'total_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'payable_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'gst_applicable' => 'boolean',
        'gst_percent' => 'decimal:2',
        'gst_amount' => 'decimal:2',
    ];

    protected $fillable = [
        'branch_id',
        'student_id',
        'course_id',
        'batch_id',
        'fee_structure_id',
        'fee_installment_id',
        'event_enrollment_id',
        'fee_account_id',
        'concession_id',
        'collected_by_id',
        'receipt_no',
        'receipt_academic_year',
        'receipt_sequence_no',
        'total_fee',
        'discount',
        'payable_amount',
        'paid_amount',
        'due_amount',
        'gst_applicable',
        'gst_percent',
        'gst_amount',
        'payment_mode',
        'cheque_number',
        'cheque_date',
        'cheque_bank_name',
        'upi_txn_ref',
        'neft_rtgs_imps_utr',
        'neft_rtgs_imps_bank_name',
        'card_gateway_ref',
        'other_reference',
        'payment_date',
        'payment_status',
        'remarks',
        'cancelled_at',
        'cancelled_by_id',
        'cancel_reason',
        'created_at',
        'updated_at',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class, 'fee_structure_id');
    }

    public function feeInstallment()
    {
        return $this->belongsTo(FeeInstallment::class, 'fee_installment_id');
    }

    public function eventEnrollment()
    {
        return $this->belongsTo(EventEnrollment::class);
    }

    public function feeAccount()
    {
        return $this->belongsTo(FeeAccount::class, 'fee_account_id');
    }

    public function concession()
    {
        return $this->belongsTo(Concession::class, 'concession_id');
    }

    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by_id');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by_id');
    }

    public function allocations()
    {
        return $this->hasMany(FeePaymentAllocation::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * How much of this payment can still be refunded through the "refund against a payment" path:
     * 0 for a cancelled payment (already voided, refunding it too would double-book the
     * reversal), otherwise whatever this payment actually applied toward fees — paid_amount
     * MINUS whatever part of it became advance/credit (see StudentCreditTransaction,
     * source=overpayment) — minus whatever has already been refunded (completed refunds only).
     * The credit-attributed portion is deliberately excluded here: if still unspent it remains
     * refundable via the separate "refund from advance/credit balance" path (fee_payment_id
     * null), which checks against the ledger's advance_balance instead. If it's already been
     * spent (applied to another installment), it isn't refundable through either path without
     * first reversing that application — a known, accepted limitation rather than an attempt to
     * silently net two different transactions together.
     */
    public function refundableAmount(): float
    {
        if ($this->payment_status === 'cancelled') {
            return 0;
        }

        $creditGenerated = (float) $this->creditTransactions()
            ->where('type', 'credit')
            ->where('source', 'overpayment')
            ->sum('amount');

        $collectable = max((float) $this->paid_amount - $creditGenerated, 0);

        return max($collectable - (float) $this->refunds()->where('status', 'completed')->sum('amount'), 0);
    }

    public function creditTransactions()
    {
        return $this->hasMany(StudentCreditTransaction::class);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
