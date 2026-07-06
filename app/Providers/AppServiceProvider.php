<?php

namespace App\Providers;

use App\Models\User;
use App\Services\WhatsappService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::saved(function (User $user) {
            if (! Schema::hasTable('whatsapp_notification_logs')) {
                return;
            }

            if (! $user->phone || ! $user->branch_id) {
                return;
            }

            try {
                $whatsapp = app(WhatsappService::class);

                if (! $whatsapp->welcomeMessageAlreadyLogged($user)) {
                    $whatsapp->sendWelcomeMessage($user);
                }
            } catch (Throwable $exception) {
                report($exception);
            }
        });
    }
}
