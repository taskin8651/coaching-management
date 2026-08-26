<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class FeeStructureInstallment extends Model
{
    public $table = 'fee_structure_installments';

    protected $fillable = [
        'fee_structure_id',
        'fee_account_id',
        'title',
        'sequence',
        'amount_type',
        'amount',
        'percentage',
        'due_date',
        'late_fee_enabled',
        'late_fee_type',
        'late_fee_amount',
        'late_fee_percentage',
        'late_fee_grace_days',
        'late_fee_max_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'due_date' => 'date',
        'late_fee_enabled' => 'boolean',
        'late_fee_amount' => 'decimal:2',
        'late_fee_percentage' => 'decimal:2',
        'late_fee_max_amount' => 'decimal:2',
    ];

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function feeAccount()
    {
        return $this->belongsTo(FeeAccount::class);
    }

    /**
     * Converts a percentage-type row into a currency amount against the given structure
     * total; fixed-type rows just return their stored amount unchanged.
     */
    public function resolvedAmount(float $structureTotal): float
    {
        if ($this->amount_type === 'percentage') {
            return round($structureTotal * ((float) $this->percentage) / 100, 2);
        }

        return (float) $this->amount;
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
