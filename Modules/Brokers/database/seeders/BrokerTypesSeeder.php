<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Brokers\Enums\BrokerType;
class BrokerTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $this->call([]);
        DB::table('broker_types')->insert([
            [
                "id" => 1,
                "name" => BrokerType::BROKER->value
               
            ],
            [
                "id" => 2,
                "name" => BrokerType::CRYPTO->value
            ],
            [
                "id" => 3,
                "name" => BrokerType::PROP_FIRM->value
            ]
        ]);
    }
}
