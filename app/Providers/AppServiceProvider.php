<?php

namespace App\Providers;

use App\Services\AiService;
//use Illuminate\Auth\Notifications\ResetPassword;
//use Illuminate\Hashing\HashManager;
use App\Services\ShaHasher;
use App\Services\StorageService;
use App\Utilities\CloudFlareClient;
use App\Utilities\OpenAiClient;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use OpenAI;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AiService::class, function ($app) {
            $openAiClient = new OpenAiClient(
                OpenAI::client(config('services.openai.api_key')),
            );

            return new AiService($openAiClient);
        });

        $this->app->singleton(StorageService::class, function ($app) {
            $cloudFlareClient = new CloudFlareClient(
                new S3Client([
                    'version' => 'latest',
                    'region' => 'auto',
                    'endpoint' => config('services.cloudflare.endpoint'),
                    'credentials' => [
                        'key' => config('services.cloudflare.key'),
                        'secret' => config('services.cloudflare.secret'),
                    ],
                    'use_path_style_endpoint' => true,
                    'bucket_endpoint' => true,
                    'bucket' => config('services.cloudflare.bucket'),
                ]),
            );

            return new StorageService($cloudFlareClient);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ResetPassword::createUrlUsing(function (
        //     object $notifiable,
        //     string $token,
        // ) {
        //     return config("app.frontend_url") .
        //         "/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        // });

        $this->app->make('hash')->extend('sha256', function () {
            return new ShaHasher();
        });

        if (app()->environment('local')) {
            DB::listen(function ($query) {
                Log::info($query->sql, $query->bindings, $query->time);
            });
        }

        // $this->app->extend(HashManager::class, function (HashManager $hashManager,Application $app) {
        //     return new ShaHasher;
        //    // return new DecoratedService($service);
        // });
    }
}
