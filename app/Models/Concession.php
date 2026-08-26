<?php

namespace App\Models;

use App\Traits\Auditable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class Concession extends Model
{
    use Auditable;

    public $table = 'concessions';

    protected $fillable = [
        'student_fee_ledger_id',
        'student_id',
        'type',
        'amount_type',
        'amount',
        'percentage',
        'reason',
        'approval_status',
        'approved_by_id',
        'approval_date',
        'remarks',
        'status',
        'created_by_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'approval_date' => 'date',
    ];

    public function ledger()
    {
        return $this->belongsTo(StudentFeeLedger::class, 'student_fee_ledger_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function feePayments()
    {
        return $this->hasMany(FeePayment::class);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
