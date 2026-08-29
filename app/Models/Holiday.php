<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    public $table = 'holidays';

    protected $fillable = [
        'branch_id',
        'name',
        'date',
        'type',
        'description',
        'created_by_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function scopeMandatory($query)
    {
        return $query->where('type', 'mandatory');
    }

    public function scopeForBranchOrGlobal($query, ?int $branchId)
    {
        return $query->where(fn ($q) => $q->whereNull('branch_id')->orWhere('branch_id', $branchId));
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
