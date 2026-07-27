<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Models\BiometricDeviceLog;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Teacher;
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
            DB::beginTransaction();

            try {
                $punchId = $txn['punchId'] ?? null;
                $txnTime = Carbon::parse($txn['txnDateTime'] ?? now());
                $mode = strtolower($txn['mode'] ?? $txn['punch_type'] ?? 'in');
                $userType = $this->resolveUserType($txn, $punchId);

                $deviceLog = BiometricDeviceLog::create([
                    'biometric_user_id' => $punchId,
                    'user_type'         => $userType,
                    'punch_time'        => $txnTime,
                    'punch_type'        => in_array($mode, ['in', 'out'], true) ? $mode : 'in',
                    'device_id'         => $txn['dvcId'] ?? $txn['device_id'] ?? null,
                    'raw_payload'       => $txn,
                    'processed_status'  => 'pending',
                ]);

                $service->process($deviceLog);

                $response[] = [
                    'txnId' => $txn['txnId'] ?? 0,
                    'status' => $deviceLog->fresh()->processed_status === 'processed' ? 1 : 0,
                ];

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();

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

    private function resolveUserType(array $txn, ?string $punchId): string
    {
        $potentialType = strtolower($txn['userType'] ?? $txn['user_type'] ?? $txn['type'] ?? '');

        if (in_array($potentialType, ['student', 'teacher', 'staff'], true)) {
            return $potentialType;
        }

        if (! $punchId) {
            return 'student';
        }

        if (Student::where('biometric_id', $punchId)->exists()) {
            return 'student';
        }

        if (Teacher::where('biometric_id', $punchId)->exists()) {
            return 'teacher';
        }

        if (Staff::where('biometric_id', $punchId)->exists()) {
            return 'staff';
        }

        return 'student';
    }
}