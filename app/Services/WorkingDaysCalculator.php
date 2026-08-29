<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Holiday;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class WorkingDaysCalculator
{
    /**
     * Working days = total calendar days in [start,end] minus every date that is either the
     * branch's weekly-off day-of-week OR a 'mandatory' holiday (global or branch-specific). A
     * date that is both (e.g. a mandatory holiday landing on the weekly-off day), or covered by
     * both a global and a branch-specific holiday, is only excluded once — both sources are
     * merged into one deduplicated date-string set before counting.
     */
    public function breakdown(?int $branchId, Carbon $start, Carbon $end): array
    {
        $weeklyOffDay = $branchId
            ? (int) (Branch::find($branchId)->weekly_off_day ?? Carbon::SUNDAY)
            : Carbon::SUNDAY;

        $totalDays = $start->diffInDays($end) + 1;

        $weeklyOffDates = collect(CarbonPeriod::create($start, $end))
            ->filter(fn ($d) => $d->dayOfWeek === $weeklyOffDay)
            ->map(fn ($d) => $d->toDateString());

        $holidayDates = Holiday::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->mandatory()
            ->forBranchOrGlobal($branchId)
            ->pluck('date')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->unique();

        $excludedDates = $weeklyOffDates->merge($holidayDates)->unique();
        $workingDays = max($totalDays - $excludedDates->count(), 1);

        return [
            'total_days' => $totalDays,
            'weekly_off_day' => $weeklyOffDay,
            'weekly_off_days_count' => $weeklyOffDates->count(),
            'holiday_dates' => $holidayDates->values()->all(),
            'excluded_days_count' => $excludedDates->count(),
            'working_days' => $workingDays,
        ];
    }

    public function workingDays(?int $branchId, Carbon $start, Carbon $end): int
    {
        return $this->breakdown($branchId, $start, $end)['working_days'];
    }
}
