<?php

namespace Tests\Unit;

use App\Models\StudentAttendance;
use App\Services\BiometricAttendanceService;
use PHPUnit\Framework\TestCase;

class BiometricAttendanceServiceTest extends TestCase
{
    public function test_it_normalizes_uppercase_punch_types_for_all_user_roles(): void
    {
        $service = new BiometricAttendanceService();

        $this->assertSame('in', $service->normalizePunchType('IN'));
        $this->assertSame('out', $service->normalizePunchType('OUT'));
        $this->assertSame('in', $service->normalizePunchType('in'));
        $this->assertSame('out', $service->normalizePunchType('out'));
    }

    public function test_it_treats_the_first_student_punch_as_check_in(): void
    {
        $service = new BiometricAttendanceService();
        $attendance = new StudentAttendance();

        $this->assertSame('in', $service->inferStudentPunchType($attendance, 'OUT'));
    }

    public function test_it_treats_a_second_student_punch_as_check_out(): void
    {
        $service = new BiometricAttendanceService();
        $attendance = new StudentAttendance();
        $attendance->actual_in_time = '16:10:00';

        $this->assertSame('out', $service->inferStudentPunchType($attendance, 'IN'));
    }
}
