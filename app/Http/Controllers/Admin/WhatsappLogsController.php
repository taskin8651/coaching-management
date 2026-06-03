<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappNotificationLog;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class WhatsappLogsController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('whatsapp_logs_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $logs = WhatsappNotificationLog::with('student.user')->latest()->take(500)->get();

        return view('admin.whatsappLogs.index', compact('logs'));
    }
}
