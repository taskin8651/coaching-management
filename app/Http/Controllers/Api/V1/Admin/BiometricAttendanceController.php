<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\BiometricDeviceLog;
use App\Models\WhatsappSetting;
use App\Services\BiometricAttendanceService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BiometricAttendanceController extends Controller
{
    public function store(Request $request, BiometricAttendanceService $service)
    {
        $setting = WhatsappSetting::where('status', 'active')->latest()->first();
        $expectedToken = $setting->biometric_device_token ?? null;

        if ($expectedToken) {
            $providedToken = $request->bearerToken() ?: $request->header('X-Device-Token') ?: $request->input('device_token');
            abort_if(! hash_equals($expectedToken, (string) $providedToken), Response::HTTP_UNAUTHORIZED, 'Invalid device token.');
        }

        $data = $request->validate([
            'biometric_user_id' => ['required', 'string', 'max:255'],
            'user_type' => ['required', 'in:student,staff,teacher'],
            'punch_time' => ['required', 'date'],
            'punch_type' => ['required', 'string'],
            'device_id' => ['nullable', 'string', 'max:255'],
        ]);

        $data['punch_type'] = $service->normalizePunchType($data['punch_type'] ?? null);

        $log = BiometricDeviceLog::create($data + [
            'raw_payload' => $request->all(),
            'processed_status' => 'pending',
        ]);

        $service->process($log);

        return response()->json([
            'success' => true,
            'log_id' => $log->id,
            'processed_status' => $log->fresh()->processed_status,
        ], 201);
    }
}
