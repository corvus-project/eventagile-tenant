<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Stancl\Tenancy\Database\Models\Domain;

class Helper
{
    public static function appdomain()
    {
        $url = config('app.url');
        $disallowed = array('http://', 'https://');
        return str_replace($disallowed, '', $url);
    }

    public static function tenantUrl()
    {
        $disallowed = array('http://', 'https://');
        Log::debug('Tenant Id: ' . tenant('id'));

        $domain = Domain::where('tenant_id', tenant('id'))->first()->domain ?? 'unknown';
        Log::debug('Debug domain: ' . $domain);

        foreach ($disallowed as $d) {
            if (strpos(env('APP_URL'), $d) === 0) {
                return 'http://' . $domain . '.' . str_replace($d, '', env('APP_URL'));
            }
        }

        return 'http://' . $domain . '.' . env('APP_URL');
    }

    public static function signedUrl()
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => tenant('id'),
                //'hash' => sha1($notifiable->getEmailForVerification()),
                'hash' => sha1(Auth::user()->get),
            ]
        );
    }
}
