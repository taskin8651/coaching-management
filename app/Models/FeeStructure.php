<?php

namespace App\Models;

use App\Traits\Auditable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    use HasFactory, Auditable;

    public $table = 'fee_structures';

    protected $fillable = [
        'branch_id',
        'course_id',
        'batch_id',
        'title',
        'academic_year',
        'board',
        'standard',
        'version_no',
        'root_fee_structure_id',
        'effective_from',
        'effective_to',
        'installment_allocation_override',
        'total_fee',
        'description',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'total_fee' => 'decimal:2',
        'installment_allocation_override' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    public function items()
    {
        return $this->hasMany(FeeStructureItem::class)->orderBy('sort_order');
    }

    public function installmentTemplates()
    {
        return $this->hasMany(FeeStructureInstallment::class)->orderBy('sequence');
    }

    public function ledgers()
    {
        return $this->hasMany(StudentFeeLedger::class);
    }

    public function rootFeeStructure()
    {
        return $this->belongsTo(FeeStructure::class, 'root_fee_structure_id');
    }

    /**
     * All versions sharing the same root (this row itself is the root when
     * root_fee_structure_id is null), ordered oldest to newest.
     */
    public function versions()
    {
        $rootId = $this->root_fee_structure_id ?? $this->id;

        return static::where('id', $rootId)
            ->orWhere('root_fee_structure_id', $rootId)
            ->orderBy('version_no');
    }

    public function recalculateTotal(): void
    {
        $this->update(['total_fee' => round((float) $this->items()->sum('line_total'), 2)]);
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
