<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MagicImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:magic-import';

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
        $this->call('app:export-brokers');
        $this->call( 'app:export-regulators');
        $this->call('app:export-companies');
        $this->call('app:export-deal-types');
        $this->call('app:export-urls');

        $this->info("====...switching to new database...====");
        DB::statement("use fxrebate");

        $this->info("\\\///...importing brokers to new database");
        //$this->call('db:seed',["class"=>"\\Modules\\Brokers\\Database\\Seeders\\StaticBrokersSeeder"]);

        $this->info("\\\///...importing companies to new database");
        //$this->call('db:seed',["class"=>"\\Modules\\Brokers\\Database\\Seeders\\CompaniesSeeder"]);
        
        $this->info("\\\///...importing regulators ");
       // $this->call('db:seed',["class"=>"\\Modules\\Brokers\\Database\\Seeders\\RegulatorsSeeder"]);
       
    
        $this->info("\\\///...importing dealtypes ");
       // $this->call('db:seed',["class"=>"\\Modules\\Brokers\\Database\\Seeders\\DealTypesSeeder"]);

       $this->info("\\\///...importing Option Categories");
     //  $this->call('db:seed',["class"=>"\\Modules\\Brokers\\Database\\Seeders\\OptionsCategoriesSeeder"]);

        $this->info("\\\///...importing URLs ");
        $this->call('db:seed',["class"=>"\\Modules\\Brokers\\Database\\Seeders\\UrlsSeeder"]);

       
       
    }
}