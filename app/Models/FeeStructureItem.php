<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class FeeStructureItem extends Model
{
    public $table = 'fee_structure_items';

    protected $fillable = [
        'fee_structure_id',
        'fee_head_id',
        'amount',
        'gst_applicable',
        'gst_percent',
        'gst_amount',
        'line_total',
        'sort_order',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gst_applicable' => 'boolean',
        'gst_percent' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
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
