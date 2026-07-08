<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Models\Student;
use App\Models\StudentBatch;
use App\Models\Timetable;
use App\Models\StudentAttendance;
use App\Models\BiometricDeviceLog;
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

    public function attendance(Request $request): JsonResponse
{
    $transactions = $request->input('trans', []);

    $response = [];

    foreach ($transactions as $txn) {

        DB::beginTransaction();

        try {

            $punchId = $txn['punchId'] ?? null;

            $txnTime = Carbon::parse(
                $txn['txnDateTime'] ?? now()
            );

            $mode = strtoupper(
                $txn['mode'] ?? 'IN'
            );

            /*
            |--------------------------------------------------------------------------
            | Save Raw Device Log
            |--------------------------------------------------------------------------
            */

            $deviceLog = BiometricDeviceLog::create([
                'biometric_user_id' => $punchId,
                'user_type'         => 'student',
                'punch_time'        => $txnTime,
                'punch_type'        => $mode,
                'device_id'         => $txn['dvcId'] ?? null,
                'raw_payload'       => $txn,
                'processed_status'  => 'pending',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Find Student
            |--------------------------------------------------------------------------
            */

            $student = Student::where('biometric_id', $punchId)
                ->first();

            if (!$student) {

                $deviceLog->update([
                    'processed_status' => 'ignored',
                    'processing_message' => 'Student not found',
                    'processed_at' => now(),
                ]);

                DB::commit();

                $response[] = [
                    'txnId' => $txn['txnId'],
                    'status' => 0,
                ];

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Active Batch
            |--------------------------------------------------------------------------
            */

            $studentBatch = StudentBatch::where(
                'student_id',
                $student->id
            )
            ->where('status', 'active')
            ->first();

            if (!$studentBatch) {

                $deviceLog->update([
                    'processed_status' => 'ignored',
                    'processing_message' => 'Active batch not found',
                    'processed_at' => now(),
                ]);

                DB::commit();

                $response[] = [
                    'txnId' => $txn['txnId'],
                    'status' => 0,
                ];

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Today's Timetable
            |--------------------------------------------------------------------------
            */

            $today = $txnTime->format('l'); // Monday

            $timetable = Timetable::where('batch_id', $studentBatch->batch_id)
                ->where('day_of_week', $today)
                ->where('status', 'scheduled')
                ->orderBy('start_time')
                ->first();

            /*
            |--------------------------------------------------------------------------
            | Attendance Date
            |--------------------------------------------------------------------------
            */

            $attendanceDate = $txnTime->toDateString();

            $attendance = StudentAttendance::firstOrCreate(
                [
                    'student_id'      => $student->id,
                    'batch_id'        => $studentBatch->batch_id,
                    'subject_id'      => optional($timetable)->subject_id,
                    'attendance_date' => $attendanceDate,
                ],
                [
                    'biometric_device_log_id' => $deviceLog->id,
                    'scheduled_start_time'    => optional($timetable)->start_time,
                    'scheduled_end_time'      => optional($timetable)->end_time,
                    'status'                  => 'present',
                    'source'                  => 'biometric',
                ]
            );

            if ($mode === 'IN') {

                if (!$attendance->actual_in_time) {

                    $attendance->update([
                        'actual_in_time' => $txnTime->format('H:i:s'),
                    ]);

                    app(\App\Services\WhatsappService::class)
                        ->sendStudentBiometricCheckIn(
                            $student,
                            $txnTime
                        );
                }

            } else {

                $attendance->update([
                    'actual_out_time' => $txnTime->format('H:i:s'),
                ]);

            }

            $deviceLog->update([
                'processed_status' => 'processed',
                'processing_message' => 'Attendance created',
                'processed_at' => now(),
            ]);

            DB::commit();

            $response[] = [
                'txnId' => $txn['txnId'],
                'status' => 1,
            ];

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