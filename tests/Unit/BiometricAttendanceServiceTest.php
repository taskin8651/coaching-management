<?php

namespace Tests\Unit;

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
}
