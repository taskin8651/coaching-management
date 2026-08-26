<?php

namespace App\Models;

use App\Traits\Auditable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory, Auditable;

    public $table = 'events';

    protected $fillable = [
        'branch_id',
        'name',
        'code',
        'event_type',
        'description',
        'venue',
        'start_date',
        'end_date',
        'registration_start_date',
        'registration_end_date',
        'base_fee',
        'capacity',
        'external_enrollment_allowed',
        'status',
        'created_by_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'registration_start_date' => 'date',
        'registration_end_date' => 'date',
        'base_fee' => 'decimal:2',
        'external_enrollment_allowed' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function batches()
    {
        return $this->belongsToMany(Batch::class, 'event_batches', 'event_id', 'batch_id')->withTimestamps();
    }

    public function feeRules()
    {
        return $this->hasMany(EventFeeRule::class);
    }

    public function enrollments()
    {
        return $this->hasMany(EventEnrollment::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function canEnroll(): bool
    {
        return $this->status === 'open';
    }

    /**
     * draft -> open/cancelled; open -> closed/cancelled; closed -> open (reopen)/cancelled;
     * cancelled is terminal. Exposed as explicit actions (publish/close/reopen/cancel), not a
     * generic status PATCH, so every transition is intentional and auditable.
     */
    public function allowedTransitions(): array
    {
        return match ($this->status) {
            'draft' => ['open', 'cancelled'],
            'open' => ['closed', 'cancelled'],
            'closed' => ['open', 'cancelled'],
            default => [],
        };
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
