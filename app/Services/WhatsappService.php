<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use App\Models\WhatsappNotificationLog;
use App\Models\WhatsappSetting;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Throwable;

class WhatsappService
{
    private const BIOMETRIC_CHECK_IN_TEMPLATE = 'student_biometric_check_in_new_crm';
    private const WELCOME_TEMPLATE = 'karmayoga_welcome_message_crm';

    public function sendWelcomeMessage(User $user): WhatsappNotificationLog
    {
        $user->loadMissing('branch');

        $number = $user->phone;
        $branchNumber = $user->branch->phone ?? null;
        $message = sprintf('Welcome message sent to %s.', $user->name);
        $log = $this->createLog(null, 'registration_welcome', $message, $number, $user);

        if (! $number) {
            return $this->failLog($log, 'Registered mobile number is missing.');
        }

        if (! $user->branch_id) {
            return $this->failLog($log, 'Branch is missing for this registered user.');
        }

        if (! $branchNumber) {
            return $this->failLog($log, 'Selected branch phone number is missing.');
        }

        if (! config('services.11za.enabled')) {
            return $this->failLog($log, 'WhatsApp sending is disabled.');
        }

        $apiUrl = config('services.11za.api_url');
        $authToken = config('services.11za.auth_token');
        $originWebsite = config('services.11za.origin_website');
        $sendTo = $this->normalizeIndianNumber($number);
        $branchContactNumber = $this->templateDisplayNumber($branchNumber);

        if (! $apiUrl || ! $authToken || ! $originWebsite) {
            return $this->failLog($log, '11za WhatsApp configuration is incomplete.');
        }

        try {
            $response = Http::asJson()
                ->timeout(15)
                ->post($apiUrl, [
                    'authToken' => $authToken,
                    'name' => $user->name,
                    'sendto' => $sendTo,
                    'originWebsite' => $originWebsite,
                    'templateName' => self::WELCOME_TEMPLATE,
                    'language' => config('services.11za.language', 'en'),
                    'data' => [
                        $branchContactNumber,
                    ],
                ]);

            $log->update([
                'status' => $response->successful() ? 'sent' : 'failed',
                'response' => $response->body(),
                'sent_at' => $response->successful() ? now() : null,
            ]);
        } catch (Throwable $exception) {
            $this->failLog($log, $exception->getMessage());
        }

        return $log->fresh();
    }

    public function sendStudentBiometricCheckIn(Student $student, CarbonInterface $checkInTime): WhatsappNotificationLog
    {
        $student->loadMissing(['user', 'branch']);

        $studentName = $student->user->name
            ?? $student->student_code
            ?? 'Student';
        $branchName = $student->branch->name ?? 'Karmayoga Academy';
        $number = $this->studentGuardianNumber($student);
        $message = sprintf(
            '%s checked in at %s on %s.',
            $studentName,
            $branchName,
            $checkInTime->format('d F Y, h:i A')
        );

        $log = $this->createLog($student, 'biometric_check_in', $message, $number);

        if (! $number) {
            return $this->failLog($log, 'Guardian WhatsApp/phone number is missing.');
        }

        $apiUrl = config('services.11za.api_url');
        $authToken = config('services.11za.auth_token');
        $originWebsite = config('services.11za.origin_website');

        if (! $apiUrl || ! $authToken || ! $originWebsite) {
            return $this->failLog($log, '11za WhatsApp configuration is incomplete.');
        }

        try {
            $response = Http::asJson()
                ->timeout(15)
                ->post($apiUrl, [
                    'authToken' => $authToken,
                    'name' => $student->guardian_name ?: $studentName,
                    'sendto' => $this->normalizeIndianNumber($number),
                    'originWebsite' => $originWebsite,
                    'templateName' => self::BIOMETRIC_CHECK_IN_TEMPLATE,
                    'language' => config('services.11za.language', 'en'),
                    'data' => [
                        $studentName,
                        $branchName,
                        $checkInTime->format('d F Y, h:i A'),
                    ],
                ]);

            $log->update([
                'status' => $response->successful() ? 'sent' : 'failed',
                'response' => $response->body(),
                'sent_at' => $response->successful() ? now() : null,
            ]);
        } catch (Throwable $exception) {
            $this->failLog($log, $exception->getMessage());
        }

        return $log->fresh();
    }

    public function sendStudentGuardianMessage(Student $student, string $module, string $message): WhatsappNotificationLog
    {
        $number = $this->studentGuardianNumber($student);
        $log = $this->createLog($student, $module, $message, $number);

        if (! $number) {
            return $this->failLog($log, 'Guardian WhatsApp/phone number is missing.');
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

    private function studentGuardianNumber(Student $student): ?string
    {
        return $student->notification_phone
            ?: $student->guardian_whatsapp
            ?: $student->guardian_phone
            ?: $student->father_phone
            ?: $student->mother_phone
            ?: $student->student_personal_phone
            ?: $student->phone
            ?: $student->alternate_phone;
    }

    public function welcomeMessageAlreadyLogged(User $user): bool
    {
        if (! Schema::hasTable('whatsapp_notification_logs')) {
            return false;
        }

        return WhatsappNotificationLog::query()
            ->where('module_name', 'registration_welcome')
            ->when(
                Schema::hasColumn('whatsapp_notification_logs', 'user_id'),
                fn ($query) => $query->where('user_id', $user->id),
                fn ($query) => $query->where('guardian_number', $user->phone)
            )
            ->exists();
    }

    private function createLog(?Student $student, string $module, string $message, ?string $number, ?User $user = null): WhatsappNotificationLog
    {
        $data = [
            'student_id' => $student?->id,
            'guardian_number' => $number,
            'module_name' => $module,
            'message' => $message,
            'status' => 'pending',
        ];

        if ($user && Schema::hasColumn('whatsapp_notification_logs', 'user_id')) {
            $data['user_id'] = $user->id;
        }

        return WhatsappNotificationLog::create($data);
    }

    private function failLog(WhatsappNotificationLog $log, string $response): WhatsappNotificationLog
    {
        $log->update([
            'status' => 'failed',
            'response' => $response,
        ]);

        return $log;
    }

    private function normalizeIndianNumber(string $number): string
    {
        $digits = preg_replace('/\D+/', '', $number) ?: '';

        if (strlen($digits) === 10) {
            return '91'.$digits;
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return $digits;
        }

        return ltrim($digits, '0');
    }

    private function templateDisplayNumber(string $number): string
    {
        $digits = preg_replace('/\D+/', '', $number) ?: '';

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return substr($digits, 2);
        }

        return ltrim($digits, '0');
    }
}
