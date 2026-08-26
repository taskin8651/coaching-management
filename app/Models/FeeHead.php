<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class FeeHead extends Model
{
    public $table = 'fee_heads';

    protected $fillable = [
        'code',
        'name',
        'description',
        'gst_applicable',
        'default_gst_percent',
        'status',
    ];

    protected $casts = [
        'gst_applicable' => 'boolean',
        'default_gst_percent' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(FeeStructureItem::class);
    }

    public function installmentItems()
    {
        return $this->hasMany(FeeInstallmentItem::class);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
