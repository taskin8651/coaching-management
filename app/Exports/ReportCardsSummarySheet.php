<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;

class ReportCardsSummarySheet implements FromView, ShouldAutoSize, WithCharts, WithTitle
{
    public function __construct(private array $summary)
    {
    }

    public function view(): View
    {
        return view('admin.reportCards.export-summary', ['summary' => $this->summary]);
    }

    public function title(): string
    {
        return 'Summary';
    }

    /**
     * Row layout produced by the export-summary view — kept in sync manually since the
     * chart cell references below point directly at these rows:
     *   4  Metric | Value header
     *   7  Passed | pass_count
     *   8  Failed | fail_count
     *   11 Grade | Count header
     *   12..(11+N) grade rows
     *
     * @return Chart[]
     */
    public function charts()
    {
        $sheet = "'Summary'";
        $charts = [];

        if (($this->summary['pass_count'] + $this->summary['fail_count']) > 0) {
            $categories = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "{$sheet}!\$A\$7:\$A\$8", null, 2)];
            $values = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "{$sheet}!\$B\$7:\$B\$8", null, 2)];

            $series = new DataSeries(DataSeries::TYPE_PIECHART, null, [0], [], $categories, $values);

            $chart = new Chart(
                'passFailChart',
                new Title('Pass vs Fail'),
                new Legend(Legend::POSITION_RIGHT, null, false),
                new PlotArea(null, [$series])
            );

            $chart->setTopLeftPosition('D2');
            $chart->setBottomRightPosition('L20');

            $charts[] = $chart;
        }

        $gradeCount = count($this->summary['grade_counts']);

        if ($gradeCount > 0) {
            $startRow = 12;
            $endRow = 11 + $gradeCount;

            $categories = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "{$sheet}!\$A\${$startRow}:\$A\${$endRow}", null, $gradeCount)];
            $values = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "{$sheet}!\$B\${$startRow}:\$B\${$endRow}", null, $gradeCount)];

            $series = new DataSeries(DataSeries::TYPE_BARCHART, DataSeries::GROUPING_CLUSTERED, [0], [], $categories, $values);

            $chart = new Chart(
                'gradeChart',
                new Title('Grade Distribution'),
                new Legend(Legend::POSITION_RIGHT, null, false),
                new PlotArea(null, [$series])
            );

            $chart->setTopLeftPosition('D22');
            $chart->setBottomRightPosition('L40');

            $charts[] = $chart;
        }

        return $charts;
    }
}
