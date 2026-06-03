<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeInstallment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['student_id', 'fee_structure_id', 'title', 'amount', 'paid_amount', 'due_amount', 'due_date', 'status', 'reminded_at'];
    protected $casts = ['amount' => 'decimal:2', 'paid_amount' => 'decimal:2', 'due_amount' => 'decimal:2'];
    protected $dates = ['due_date', 'reminded_at', 'created_at', 'updated_at', 'deleted_at'];

    public function student() { return $this->belongsTo(Student::class); }
    public function feeStructure() { return $this->belongsTo(FeeStructure::class); }
}
