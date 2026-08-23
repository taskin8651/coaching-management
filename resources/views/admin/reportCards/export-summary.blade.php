<table>
    <tr>
        <td colspan="2" style="background-color:#1E3A8A;color:#FFFFFF;font-weight:bold;font-size:16px;">Report Cards — Summary Report</td>
    </tr>
    <tr>
        <td colspan="2" style="color:#64748B;font-style:italic;">Generated on {{ now()->format('d M Y, H:i') }}</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td style="background-color:#2563EB;color:#FFFFFF;font-weight:bold;border:1px solid #1E40AF;">Metric</td>
        <td style="background-color:#2563EB;color:#FFFFFF;font-weight:bold;border:1px solid #1E40AF;">Value</td>
    </tr>
    <tr>
        <td style="border:1px solid #CBD5E1;">Total Report Cards</td>
        <td style="border:1px solid #CBD5E1;">{{ $summary['total'] }}</td>
    </tr>
    <tr>
        <td style="border:1px solid #CBD5E1;">Average Percentage</td>
        <td style="border:1px solid #CBD5E1;">{{ $summary['average_percentage'] }}%</td>
    </tr>
    <tr>
        <td style="border:1px solid #CBD5E1;">Passed</td>
        <td style="border:1px solid #CBD5E1;color:#15803D;font-weight:bold;">{{ $summary['pass_count'] }}</td>
    </tr>
    <tr>
        <td style="border:1px solid #CBD5E1;">Failed</td>
        <td style="border:1px solid #CBD5E1;color:#B91C1C;font-weight:bold;">{{ $summary['fail_count'] }}</td>
    </tr>
    <tr>
        <td style="border:1px solid #CBD5E1;">Pass Percentage</td>
        <td style="border:1px solid #CBD5E1;">{{ $summary['pass_percentage'] }}%</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td style="background-color:#2563EB;color:#FFFFFF;font-weight:bold;border:1px solid #1E40AF;">Grade</td>
        <td style="background-color:#2563EB;color:#FFFFFF;font-weight:bold;border:1px solid #1E40AF;">Count</td>
    </tr>
    @forelse($summary['grade_counts'] as $grade => $count)
        <tr>
            <td style="border:1px solid #CBD5E1;">{{ $grade }}</td>
            <td style="border:1px solid #CBD5E1;">{{ $count }}</td>
        </tr>
    @empty
        <tr>
            <td style="border:1px solid #CBD5E1;" colspan="2">No report cards found for the selected filters.</td>
        </tr>
    @endforelse
</table>
