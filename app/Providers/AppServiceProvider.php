<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Mail\Transport\BrevoTransport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Evita rodar durante package:discover / CLI
        if (!app()->runningInConsole()) {
            Mail::extend('brevo', function ($app) {
                $transport = new BrevoTransport(env('MAIL_BREVO_API_KEY'));
                $swiftMailer = new \Swift_Mailer($transport);
                return new \Illuminate\Mail\Mailer($app['view'], $swiftMailer, $app['events']);
            });
        }
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
