<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class FeeInstallmentItem extends Model
{
    public $table = 'fee_installment_items';

    protected $fillable = [
        'fee_installment_id',
        'fee_head_id',
        'amount',
        'gst_percent',
        'gst_amount',
        'line_total',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gst_percent' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function feeInstallment()
    {
        return $this->belongsTo(FeeInstallment::class);
    }

    public function feeHead()
    {
        return $this->belongsTo(FeeHead::class);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
