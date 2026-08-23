<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReportCardsExport implements WithMultipleSheets
{
    public function __construct(private Collection $reportCards, private array $summary)
    {
    }

    public function sheets(): array
    {
        return [
            'Summary'      => new ReportCardsSummarySheet($this->summary),
            'Report Cards' => new ReportCardsListSheet($this->reportCards),
        ];
    }
}
