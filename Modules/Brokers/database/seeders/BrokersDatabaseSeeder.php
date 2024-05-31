<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;

use Modules\Brokers\Database\Seeders\BrokersRegulatorsSeeder;

class BrokersDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //OptionsCategoriesSeeder::class
        $this->call([
          //  StaticBrokersSeeder::class,
           // CompaniesSeeder::class,
            //RegulatorsSeeder::class,
          // BrokersRegulatorsSeeder::class,
          // DealTypesSeeder::class
        ]);
       
    }
}

//php artisan db:seed --class=\\Modules\\Brokers\\Database\\Seeders\\DatabaseSeeder