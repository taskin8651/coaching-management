<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeInstallment extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'fee_structure_id', 'title', 'amount', 'paid_amount', 'due_amount', 'due_date', 'status', 'reminded_at'];
    protected $casts = ['amount' => 'decimal:2', 'paid_amount' => 'decimal:2', 'due_amount' => 'decimal:2'];
    protected $dates = ['due_date', 'reminded_at', 'created_at', 'updated_at'];

    public function student() { return $this->belongsTo(Student::class); }
    public function feeStructure() { return $this->belongsTo(FeeStructure::class); }
    public function payments() { return $this->hasMany(FeePayment::class, 'fee_installment_id'); }

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
     * Re-sums ALL linked FeePayment rows (not an incremental add/subtract) so this stays
     * correct no matter how many payments get linked, edited, re-linked or deleted — the
     * caller only needs to trigger this after any change to a payment's fee_installment_id
     * or paid_amount, not track deltas itself.
     */
    public function recalculateFromPayments(): void
    {
        $paidAmount = (float) $this->payments()->sum('paid_amount');
        $dueAmount = max(((float) $this->amount) - $paidAmount, 0);

        $status = $this->status;

        if ($dueAmount <= 0) {
            $status = 'paid';
        } elseif ($paidAmount > 0) {
            $status = 'partial';
        } elseif (in_array($this->status, ['paid', 'partial'])) {
            // Every linked payment was removed/unlinked — fall back to pending (not overdue;
            // getDisplayStatusAttribute() derives overdue from due_date at read time).
            $status = 'pending';
        }

        $this->update([
            'paid_amount' => round($paidAmount, 2),
            'due_amount' => round($dueAmount, 2),
            'status' => $status,
        ]);
    }
}
