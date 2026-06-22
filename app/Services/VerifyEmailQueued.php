<?php

namespace App\Services;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

// https://github.com/archtechx/tenancy/issues/851
class VerifyEmailQueued extends VerifyEmail
{
    //use Queueable;

    protected function verificationUrl($notifiable)
    {
        Log::debug('Generating verification URL for user', ['user_id' => $notifiable->i, 'tenant_id' => tenant('id')]);
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable);
        }
        if (tenant('id'))
            URL::forceRootUrl(Helper::tenantUrl());

        Log::debug('Tenant URL: ' . Helper::tenantUrl());

        $url = URL::temporarySignedRoute(
            'tenant.verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
        Log::debug('Generated verification URL', ['url' => $url]);
        return $url;
    }
}
