<?php

namespace App\Providers;

use App\Repositories\RepositoryInterface;
use App\Services\ShaHasher;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Console\Application;
use Illuminate\Hashing\HashManager;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ServiceProvider;
use App\Services\AiService;
use OpenAI;
use OpenAI\Client;
use App\Utilities\OpenAiClient;
use App\Services\StorageService;
use App\Utilities\CloudFlareClient;
use Aws\S3\S3Client;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AiService::class, function ($app) {
            $openAiClient = new OpenAiClient(OpenAI::client(config('services.openai.api_key')));
            return new AiService($openAiClient);
           
        });

        $this->app->singleton(StorageService::class, function ($app) {

            $cloudFlareClient=new CloudFlareClient(new S3Client([
                'version' => 'latest',
                'region'  => 'auto',
                'endpoint' => config('services.cloudflare.endpoint'),
                'credentials' => [
                    'key'    => config('services.cloudflare.key'),
                    'secret' => config('services.cloudflare.secret'),
                ],
                'use_path_style_endpoint' => true,
                'bucket_endpoint' => true,
                'bucket' => config('services.cloudflare.bucket')
            ]));

            return new StorageService($cloudFlareClient);
        });
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
