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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Mail::extend('brevo', function($app) {
            $transport = new BrevoTransport(env('MAIL_BREVO_API_KEY'));
            $swiftMailer = new \Swift_Mailer($transport);

            // Cria o Mailer do Laravel passando o Swift_Mailer customizado
            $mailer = new \Illuminate\Mail\Mailer($app['view'], $swiftMailer, $app['events']);

            return $mailer;
        });

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
