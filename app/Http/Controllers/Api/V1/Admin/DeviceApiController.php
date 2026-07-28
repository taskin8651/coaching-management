<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Models\Student;
use App\Models\Staff;
use App\Models\Teacher;
use App\Models\StudentBatch;
use App\Models\Timetable;
use App\Models\StudentAttendance;
use App\Models\BiometricDeviceLog;
use App\Services\BiometricAttendanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DeviceApiController extends Controller
{
    public function heartbeat(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 1
        ]);
    }

    public function attendance(Request $request, BiometricAttendanceService $service): JsonResponse
    {
        $transactions = $request->input('trans', []);
        $response = [];

        foreach ($transactions as $txn) {
            try {
                $punchId = $txn['punchId'] ?? null;
                $txnTime = Carbon::parse($txn['txnDateTime'] ?? now());
                $mode = $service->normalizePunchType($txn['mode'] ?? 'IN');

                // Detect user type by checking Student, Staff, Teacher
                $userType = $this->detectUserType($punchId);

                if (!$userType) {
                    $response[] = [
                        'txnId' => $txn['txnId'],
                        'status' => 0,
                    ];
                    continue;
                }

                // Create device log with correct user type
                $deviceLog = BiometricDeviceLog::create([
                    'biometric_user_id' => $punchId,
                    'user_type'         => $userType,
                    'punch_time'        => $txnTime,
                    'punch_type'        => $mode,
                    'device_id'         => $txn['dvcId'] ?? null,
                    'raw_payload'       => $txn,
                    'processed_status'  => 'pending',
                ]);

                // Process using BiometricAttendanceService
                $service->process($deviceLog);

                $response[] = [
                    'txnId' => $txn['txnId'],
                    'status' => 1,
                ];

            } catch (\Throwable $e) {
                \Log::error('BIOMETRIC ATTENDANCE ERROR', [
                    'txn' => $txn,
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                ]);

                $response[] = [
                    'txnId' => $txn['txnId'] ?? 0,
                    'status' => 0,
                ];
            }
        }

        return response()->json([
            'transStatus' => $response
        ]);
    }

    /**
     * Detect user type by checking if biometric_id exists in Student, Staff, or Teacher
     */
    private function detectUserType(string $biometricId): ?string
    {
        if (Student::where('biometric_id', $biometricId)->exists()) {
            return 'student';
        }

        if (Staff::where('biometric_id', $biometricId)->exists()) {
            return 'staff';
        }

        if (Teacher::where('biometric_id', $biometricId)->exists()) {
            return 'teacher';
        }

        return null;
    }

    private function decode(Request $request): array
    {
        $payload = $request->all();

        if (!empty($payload)) {
            return $payload;
        }

        $json = json_decode($request->getContent(), true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            return $json;
        }

        return [];
    }
}