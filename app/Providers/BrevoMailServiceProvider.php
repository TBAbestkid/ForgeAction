<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Mail;
use App\Mail\Transport\BrevoTransport;

class BrevoMailServiceProvider extends ServiceProvider
{
    public function register()
    {
        if (!app()->runningInConsole()) {
            Mail::extend('brevo', function ($app) {
                $transport = new BrevoTransport(env('MAIL_BREVO_API_KEY'));
                $swiftMailer = new \Swift_Mailer($transport);
                return new \Illuminate\Mail\Mailer($app['view'], $swiftMailer, $app['events']);
            });
        }
    }

    public function boot() {}
}
