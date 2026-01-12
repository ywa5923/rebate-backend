<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LoadDefaultData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:load-default-data';

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
        DB::statement("use fxrebate");
        $this->info("\\\\///...importing Option Categories");
        $this->call('db:seed', ["class" => "\\Modules\\Brokers\\Database\\Seeders\\OptionsCategoriesSeeder"]);

        $this->info("\\\\///...importing URLs ");
       // $this->call('db:seed', ["class" => "\\Modules\\Brokers\\Database\\Seeders\\UrlsSeeder"]);

        $this->info("\\\\///...importing broker_options ");
        $this->call('db:seed', ["class" => "\\Modules\\Brokers\\Database\\Seeders\DynamicOptionsSeeder"]);

         
        $this->info("\\\\///...importing broker_types ");
        $this->call('db:seed', ["class" => "\\Modules\\Brokers\\Database\\Seeders\BrokerTypesSeeder"]);

        $this->info("\\\\///...importing settings table ");
        $this->call('db:seed', ["class" => "\\Modules\\Brokers\\Database\\Seeders\SettingsSeeder"]);

        $this->info("\\\\///...importing zones table ");
        $this->call('db:seed', ["class" => "\\Modules\\Brokers\\Database\\Seeders\ZonesSeeder"]);

        $this->info("\\\\///...importing dropdowns ");
        $this->call('db:seed', ["class" => "\\Modules\\Brokers\\Database\\Seeders\DropdownSeeder"]);

        $this->info("\\\\///...importing form types ");
        $this->call('db:seed', ["class" => "\\Modules\\Brokers\\Database\\Seeders\FormTypesSeeder"]);

        $this->info("\\\\///...importing matrix headers");
        $this->call('db:seed', ["class" => "\\Modules\\Brokers\\Database\\Seeders\MatrixSeeder"]);

        $this->info("\\\\///...importing matrix headers 2");
        $this->call('db:seed', ["class" => "\\Modules\\Brokers\\Database\\Seeders\MatrixHeadearsSeeder"]);

        $this->info("\\\\///...importing challenge categories");
        $this->call('db:seed', ["class" => "\\Modules\\Brokers\\Database\\Seeders\ChallengeSeeder"]);

    }
}
