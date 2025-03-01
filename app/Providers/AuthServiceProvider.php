<?php

namespace App\Providers; // Ensure correct namespace

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        $this->registerPolicies();

        VerifyEmail::createUrlUsing(function ($notifiable) {
            if (!$notifiable) {
                throw new \Exception("Notifiable is null when generating email verification link.");
            }

            return URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(Config::get('auth.verification.expire', 60)),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );
        });
    }
}