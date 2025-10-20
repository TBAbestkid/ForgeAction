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
            return new \Illuminate\Mail\Mailer(
                $app['view'],
                $app['swift.mailer']->getTransport() // Não usado, mas obrigatório
            )->setSwiftMailer(new \Swift_Mailer(new BrevoTransport(env('MAIL_BREVO_API_KEY'))));
        });

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
