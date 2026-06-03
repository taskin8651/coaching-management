<?php

namespace App\Services;

use App\Models\Student;
use App\Models\WhatsappNotificationLog;
use App\Models\WhatsappSetting;
use Illuminate\Support\Facades\Http;
use Throwable;

class WhatsappService
{
    public function sendStudentGuardianMessage(Student $student, string $module, string $message): WhatsappNotificationLog
    {
        $number = $student->guardian_whatsapp
            ?: $student->guardian_phone
            ?: $student->phone
            ?: $student->alternate_phone;

        $log = WhatsappNotificationLog::create([
            'student_id' => $student->id,
            'guardian_number' => $number,
            'module_name' => $module,
            'message' => $message,
            'status' => 'pending',
        ]);

        if (! $number) {
            $log->update([
                'status' => 'failed',
                'response' => 'Guardian WhatsApp/phone number is missing.',
            ]);

            return $log;
        }

        $setting = WhatsappSetting::where('status', 'active')->latest()->first();

        if (! $setting || ! $setting->api_base_url) {
            $log->update([
                'status' => 'failed',
                'response' => 'Active WhatsApp API setting is missing.',
            ]);

            return $log;
        }

        try {
            $response = Http::timeout(10)
                ->withToken((string) $setting->api_key)
                ->post($setting->api_base_url, [
                    'provider' => $setting->api_provider,
                    'sender' => $setting->sender_number,
                    'to' => $number,
                    'message' => $message,
                    'module' => $module,
                ]);

            $log->update([
                'status' => $response->successful() ? 'sent' : 'failed',
                'response' => $response->body(),
                'sent_at' => $response->successful() ? now() : null,
            ]);
        } catch (Throwable $exception) {
            $log->update([
                'status' => 'failed',
                'response' => $exception->getMessage(),
            ]);
        }

        return $log;
    }
}
