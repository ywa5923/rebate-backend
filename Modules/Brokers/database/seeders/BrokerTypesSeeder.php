<?php

namespace Modules\Brokers\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
                "name" => "Brokers"
               
            ],
            [
                "id" => 2,
                "name" => "Crypto"
            ],
            [
                "id" => 3,
                "name" => "Prop Firms"
            ]
        ]);
    }
}
