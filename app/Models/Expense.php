<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    public $table = 'expenses';

    protected $dates = [
        'expense_date',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    protected $fillable = [
        'branch_id',
        'paid_by_id',
        'title',
        'category',
        'amount',
        'expense_date',
        'payment_mode',
        'vendor_name',
        'bill_no',
        'status',
        'remarks',
        'created_at',
        'updated_at',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}