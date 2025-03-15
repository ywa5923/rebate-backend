<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Translations\Models\LocaleResource;
use Modules\Translations\Models\Translation;

class LoadLocales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:load-locales';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
       Translation::where('translationable_type', LocaleResource::class)->delete();
        LocaleResource::query()->delete();
         $this->call('db:seed', ["class" => "\\Modules\\Translations\\Database\\Seeders\\LocaleResourceSeeder"]);
    }
}
