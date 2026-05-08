<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeePayment extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'fee_payments';

    protected $dates = [
        'payment_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'total_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'payable_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
    ];

    protected $fillable = [
        'branch_id',
        'student_id',
        'course_id',
        'batch_id',
        'fee_structure_id',
        'collected_by_id',
        'receipt_no',
        'total_fee',
        'discount',
        'payable_amount',
        'paid_amount',
        'due_amount',
        'payment_mode',
        'payment_date',
        'payment_status',
        'remarks',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class, 'fee_structure_id');
    }

    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}