<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class EventFeeRule extends Model
{
    public $table = 'event_fee_rules';

    protected $fillable = [
        'event_id',
        'rule_type',
        'label',
        'amount',
        'min_group_size',
        'valid_until',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'valid_until' => 'date',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Picks the cheapest active rule whose conditions match this participant/group/date,
     * falling back to the event's base_fee when nothing matches. Complimentary is deliberately
     * NOT a rule here — it's an explicit per-enrollment override applied by the caller before
     * this is ever consulted (see EventEnrollmentsController::markComplimentary).
     */
    public static function resolveFor(Event $event, string $participantType, ?int $groupSize, DateTimeInterface $date): array
    {
        $groupSize = $groupSize ?? 1;
        $dateStr = $date->format('Y-m-d');

        $best = $event->feeRules()
            ->where('status', 'active')
            ->get()
            ->filter(function (self $rule) use ($participantType, $groupSize, $dateStr) {
                return match ($rule->rule_type) {
                    'karmayoga_student' => $participantType === 'student',
                    'external_student' => $participantType === 'external',
                    'group' => $rule->min_group_size !== null && $groupSize >= $rule->min_group_size,
                    'early_bird' => $rule->valid_until && $dateStr <= $rule->valid_until->format('Y-m-d'),
                    default => false,
                };
            })
            ->sortBy('amount')
            ->first();

        if ($best) {
            return [
                'amount' => (float) $best->amount,
                'label' => $best->label ?: ucfirst(str_replace('_', ' ', $best->rule_type)),
            ];
        }

        return ['amount' => (float) $event->base_fee, 'label' => 'Base Fee'];
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
