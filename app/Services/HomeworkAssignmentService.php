<?php

namespace App\Services;

use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Student;

class HomeworkAssignmentService
{
    public function assignToStudents(Homework $homework, WhatsappService $whatsapp): void
    {
        $students = Student::where(function ($query) use ($homework) {
            $query->where('batch_id', $homework->batch_id)
                ->orWhereHas('studentBatches', fn ($q) => $q->where('batch_id', $homework->batch_id)->where('status', 'active'));
        })
            ->when($homework->branch_id, fn ($query) => $query->where('branch_id', $homework->branch_id))
            ->get();

        foreach ($students as $student) {
            HomeworkSubmission::firstOrCreate(
                ['unique_key' => HomeworkSubmission::makeUniqueKey($homework->id, $student->id)],
                ['homework_id' => $homework->id, 'student_id' => $student->id]
            );
            $whatsapp->sendStudentGuardianMessage($student, 'homework', 'Homework assigned: '.$homework->title.' due on '.optional($homework->due_date)->format('d M Y'));
        }
    }
}
