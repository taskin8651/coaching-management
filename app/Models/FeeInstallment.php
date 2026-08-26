<?php

namespace App\Models;

use App\Traits\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeInstallment extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'student_id', 'fee_structure_id', 'student_fee_ledger_id', 'fee_structure_installment_id',
        'fee_account_id', 'title', 'amount', 'paid_amount', 'due_amount', 'due_date', 'status', 'reminded_at',
        'late_fee_enabled', 'late_fee_type', 'late_fee_amount', 'late_fee_percentage',
        'late_fee_grace_days', 'late_fee_max_amount', 'late_fee_applied_amount',
        'late_fee_applied_at', 'late_fee_applied_by_id',
    ];
    protected $casts = [
        'amount' => 'decimal:2', 'paid_amount' => 'decimal:2', 'due_amount' => 'decimal:2',
        'late_fee_enabled' => 'boolean', 'late_fee_amount' => 'decimal:2', 'late_fee_percentage' => 'decimal:2',
        'late_fee_max_amount' => 'decimal:2', 'late_fee_applied_amount' => 'decimal:2',
    ];
    protected $dates = ['due_date', 'reminded_at', 'late_fee_applied_at', 'created_at', 'updated_at'];

    public function student() { return $this->belongsTo(Student::class); }
    public function feeStructure() { return $this->belongsTo(FeeStructure::class); }
    public function ledger() { return $this->belongsTo(StudentFeeLedger::class, 'student_fee_ledger_id'); }
    public function structureInstallment() { return $this->belongsTo(FeeStructureInstallment::class, 'fee_structure_installment_id'); }
    public function feeAccount() { return $this->belongsTo(FeeAccount::class); }
    public function payments() { return $this->hasMany(FeePayment::class, 'fee_installment_id'); }
    public function items() { return $this->hasMany(FeeInstallmentItem::class); }
    public function allocations() { return $this->hasMany(FeePaymentAllocation::class); }
    public function refunds() { return $this->hasMany(Refund::class); }
    public function lateFeeAppliedBy() { return $this->belongsTo(User::class, 'late_fee_applied_by_id'); }

    /**
     * `status` here is only ever set manually (create/edit) or by this method — it never
     * becomes 'overdue' on its own. getDisplayStatusAttribute() layers that in at read time.
     */
    public function getDisplayStatusAttribute(): string
    {
        if ($this->status === 'paid') {
            return 'paid';
        }

        if ($this->due_date && Carbon::parse($this->due_date)->isPast() && (float) $this->due_amount > 0) {
            return 'overdue';
        }

        return $this->status;
    }

    /**
     * Re-sums ALL linked, non-cancelled FeePayment rows (direct + allocated) minus completed
     * refunds — not an incremental add/subtract — so this stays correct no matter how many
     * payments/allocations/refunds get linked, edited, cancelled or completed. The raw sum is
     * capped at this installment's own `amount`: any excess is NOT this installment's money, it
     * is advance/credit (recorded separately as a StudentCreditTransaction by the caller) — so
     * paid_amount here must never exceed amount. Also keeps the parent ledger in sync.
     */
    public function recalculateFromPayments(): void
    {
        $directPaid = (float) $this->payments()->where('payment_status', '!=', 'cancelled')->sum('paid_amount');

        $allocatedPaid = (float) FeePaymentAllocation::where('fee_installment_id', $this->id)
            ->whereHas('feePayment', fn ($q) => $q->where('payment_status', '!=', 'cancelled'))
            ->sum('amount');

        $refunded = (float) $this->refunds()->where('status', 'completed')->sum('amount');

        $rawPaid = max($directPaid + $allocatedPaid - $refunded, 0);
        $paidAmount = min($rawPaid, (float) $this->amount);
        $dueAmount = max(((float) $this->amount) - $paidAmount, 0);

        $status = $this->status;

        if ($dueAmount <= 0) {
            $status = 'paid';
        } elseif ($paidAmount > 0) {
            $status = 'partial';
        } elseif (in_array($this->status, ['paid', 'partial'])) {
            // Every linked payment was removed/unlinked/cancelled/refunded — fall back to
            // pending (not overdue; getDisplayStatusAttribute() derives overdue from due_date).
            $status = 'pending';
        }

        $this->update([
            'paid_amount' => round($paidAmount, 2),
            'due_amount' => round($dueAmount, 2),
            'status' => $status,
        ]);

        $this->ledger?->recalculate();
    }

    /**
     * Pure suggestion — never persists anything. Computes what the late fee WOULD be today
     * (fixed / percentage of installment amount / per-day since grace period ends), capped at
     * late_fee_max_amount if set. Callers must explicitly apply it (see
     * FeeInstallmentsController::applyLateFee) for it to actually change amount/due_amount.
     */
    public function calculateSuggestedLateFee(?Carbon $asOf = null): float
    {
        if (! $this->late_fee_enabled || ! $this->due_date || (float) $this->due_amount <= 0) {
            return 0;
        }

        $graceEnds = Carbon::parse($this->due_date)->addDays((int) $this->late_fee_grace_days);
        $asOf = $asOf ?? now();

        $overdueDays = $graceEnds->diffInDays($asOf, false);

        if ($overdueDays <= 0) {
            return 0;
        }

        $fee = match ($this->late_fee_type) {
            'fixed' => (float) $this->late_fee_amount,
            'percentage' => round(((float) $this->amount) * ((float) $this->late_fee_percentage) / 100, 2),
            'per_day' => round(((float) $this->late_fee_amount) * $overdueDays, 2),
            default => 0,
        };

        return $this->late_fee_max_amount ? min($fee, (float) $this->late_fee_max_amount) : $fee;
    }
}
