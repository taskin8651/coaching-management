<?php

namespace App\Models;

use App\Traits\Auditable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use Auditable;

    public $table = 'refunds';

    protected $fillable = [
        'student_fee_ledger_id',
        'student_id',
        'fee_payment_id',
        'fee_installment_id',
        'fee_account_id',
        'amount',
        'mode',
        'reference_no',
        'refund_date',
        'reason',
        'remarks',
        'approval_status',
        'approved_by_id',
        'approval_date',
        'status',
        'completed_by_id',
        'completed_at',
        'created_by_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refund_date' => 'date',
        'approval_date' => 'date',
        'completed_at' => 'datetime',
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

    public function feeAccount()
    {
        return $this->belongsTo(FeeAccount::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by_id');
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
