<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class ReceiptNumberSequence extends Model
{
    public $table = 'receipt_number_sequences';

    protected $fillable = [
        'branch_id',
        'academic_year',
        'last_number',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
