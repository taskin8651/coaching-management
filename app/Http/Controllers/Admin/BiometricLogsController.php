<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BiometricDeviceLog;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class BiometricLogsController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('biometric_logs_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $logs = BiometricDeviceLog::latest()->take(500)->get();

        return view('admin.biometricLogs.index', compact('logs'));
    }
}
