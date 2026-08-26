<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class FeePaymentAllocation extends Model
{
    public $table = 'fee_payment_allocations';

    protected $fillable = [
        'fee_payment_id',
        'fee_installment_id',
        'fee_installment_item_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function feePayment()
    {
        return $this->belongsTo(FeePayment::class);
    }

    public function feeInstallment()
    {
        return $this->belongsTo(FeeInstallment::class);
    }

    public function feeInstallmentItem()
    {
        return $this->belongsTo(FeeInstallmentItem::class);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
