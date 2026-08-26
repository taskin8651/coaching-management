<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class StudentFeeLedger extends Model
{
    public $table = 'student_fee_ledgers';

    protected $fillable = [
        'student_id',
        'fee_structure_id',
        'fee_structure_version',
        'net_payable',
        'concession_total',
        'paid_till_date',
        'outstanding_amount',
        'advance_balance',
        'assigned_by_id',
        'assigned_at',
        'status',
        'remarks',
    ];

    protected $casts = [
        'net_payable' => 'decimal:2',
        'concession_total' => 'decimal:2',
        'paid_till_date' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
        'advance_balance' => 'decimal:2',
        'assigned_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by_id');
    }

    public function installments()
    {
        return $this->hasMany(FeeInstallment::class);
    }

    public function concessions()
    {
        return $this->hasMany(Concession::class);
    }

    public function credits()
    {
        return $this->hasMany(StudentCreditTransaction::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Re-sums installments/concessions from scratch (not an incremental add/subtract) so this
     * stays correct regardless of how many payments/concessions get added, edited or cancelled —
     * same philosophy as FeeInstallment::recalculateFromPayments().
     */
    public function recalculate(): void
    {
        $paid = (float) $this->installments()->sum('paid_amount');

        $concessionTotal = (float) $this->concessions()
            ->where('status', 'active')
            ->where('approval_status', 'approved')
            ->get()
            ->sum(function (Concession $concession) {
                if ($concession->amount_type === 'percentage') {
                    return round(((float) $this->net_payable) * ((float) $concession->percentage) / 100, 2);
                }

                return (float) $concession->amount;
            });

        $advanceBalance = (float) $this->credits()->where('type', 'credit')->sum('amount')
            - (float) $this->credits()->where('type', 'debit')->sum('amount');

        $this->update([
            'paid_till_date' => round($paid, 2),
            'concession_total' => round($concessionTotal, 2),
            'outstanding_amount' => max(round(((float) $this->net_payable) - $concessionTotal - $paid, 2), 0),
            'advance_balance' => max(round($advanceBalance, 2), 0),
        ]);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
