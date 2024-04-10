<?php

namespace App\Providers;

use App\Services\Auth\ShaHasher;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Console\Application;
use Illuminate\Hashing\HashManager;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        $this->app->make('hash')->extend('sha256', function() {
            return new ShaHasher;
        });


        // $this->app->extend(HashManager::class, function (HashManager $hashManager,Application $app) {
        //     return new ShaHasher;
        //    // return new DecoratedService($service);
        // });
        
       
    }
}
