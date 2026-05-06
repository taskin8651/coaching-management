<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryPayment extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'salary_payments';

    protected $dates = [
        'payment_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'basic_salary' => 'decimal:2',
        'bonus' => 'decimal:2',
        'deduction' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
    ];

    protected $fillable = [
        'branch_id',
        'user_id',
        'teacher_id',
        'staff_id',
        'paid_by_id',
        'employee_type',
        'salary_month',
        'slip_no',
        'basic_salary',
        'bonus',
        'deduction',
        'net_salary',
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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by_id');
    }

    public function getEmployeeNameAttribute()
    {
        if ($this->employee_type === 'teacher') {
            return $this->teacher->user->name ?? $this->user->name ?? '-';
        }

        return $this->staff->user->name ?? $this->user->name ?? '-';
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}