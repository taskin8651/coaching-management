<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReportCardsListSheet implements FromView, ShouldAutoSize, WithTitle
{
    public function __construct(private Collection $reportCards)
    {
    }

    public function view(): View
    {
        return view('admin.reportCards.export-list', ['reportCards' => $this->reportCards]);
    }

    public function title(): string
    {
        return 'Report Cards';
    }
}
