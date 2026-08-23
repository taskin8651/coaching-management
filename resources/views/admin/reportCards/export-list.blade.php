<table>
    <tr>
        @foreach(['Student', 'Student Code', 'Exam', 'Exam Type', 'Exam Date', 'Subject', 'Course', 'Marks Obtained', 'Total Marks', 'Percentage', 'Grade', 'Result', 'Rank', 'Parent Status'] as $heading)
            <td style="background-color:#2563EB;color:#FFFFFF;font-weight:bold;border:1px solid #1E40AF;">{{ $heading }}</td>
        @endforeach
    </tr>

    @forelse($reportCards as $card)
        @php
            $result = '-';

            if ($card->exam && $card->exam->passing_marks !== null) {
                $result = $card->marks_obtained >= $card->exam->passing_marks ? 'Pass' : 'Fail';
            }

            $resultStyle = $result === 'Pass' ? 'color:#15803D;font-weight:bold;' : ($result === 'Fail' ? 'color:#B91C1C;font-weight:bold;' : '');
        @endphp
        <tr>
            <td style="border:1px solid #CBD5E1;">{{ $card->student->user->name ?? 'Student' }}</td>
            <td style="border:1px solid #CBD5E1;">{{ $card->student->student_code ?? '-' }}</td>
            <td style="border:1px solid #CBD5E1;">{{ $card->exam->title ?? '-' }}</td>
            <td style="border:1px solid #CBD5E1;">{{ $card->exam->exam_type ?? '-' }}</td>
            <td style="border:1px solid #CBD5E1;">{{ optional($card->exam->exam_date ?? null)->format('d M Y') ?? '-' }}</td>
            <td style="border:1px solid #CBD5E1;">{{ $card->exam->subject->name ?? '-' }}</td>
            <td style="border:1px solid #CBD5E1;">{{ $card->exam->course->name ?? '-' }}</td>
            <td style="border:1px solid #CBD5E1;">{{ $card->marks_obtained ?? 0 }}</td>
            <td style="border:1px solid #CBD5E1;">{{ $card->total_marks ?? 0 }}</td>
            <td style="border:1px solid #CBD5E1;">{{ number_format($card->percentage ?? 0, 2) }}%</td>
            <td style="border:1px solid #CBD5E1;">{{ $card->grade ?? '-' }}</td>
            <td style="border:1px solid #CBD5E1;{{ $resultStyle }}">{{ $result }}</td>
            <td style="border:1px solid #CBD5E1;">{{ $card->rank ?? '-' }}</td>
            <td style="border:1px solid #CBD5E1;">{{ $card->published_to_parent ? 'Published' : 'Draft' }}</td>
        </tr>
    @empty
        <tr>
            <td style="border:1px solid #CBD5E1;" colspan="14">No report cards found for the selected filters.</td>
        </tr>
    @endforelse
</table>
