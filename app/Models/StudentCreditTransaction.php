<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class StudentCreditTransaction extends Model
{
    public $table = 'student_credit_transactions';

    protected $fillable = [
        'student_fee_ledger_id',
        'student_id',
        'fee_payment_id',
        'fee_installment_id',
        'type',
        'source',
        'amount',
        'remarks',
        'created_by_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function ledger()
    {
        return $this->belongsTo(StudentFeeLedger::class, 'student_fee_ledger_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function feePayment()
    {
        return $this->belongsTo(FeePayment::class);
    }

    public function feeInstallment()
    {
        return $this->belongsTo(FeeInstallment::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
