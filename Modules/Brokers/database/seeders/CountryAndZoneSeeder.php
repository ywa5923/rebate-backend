<?php

namespace Modules\Brokers\Database\Seeders;

use Database\Seeders\BatchImporter;
use Illuminate\Database\Seeder;
use Modules\Brokers\Models\Country;
use Modules\Brokers\Models\Zone;
use RuntimeException;

class CountryAndZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->importZones();
        $this->importCountries();
    }

    public function importZones(): void
    {
        $csvFile = module_path('Brokers', 'database/seeders/csv/default/zones.csv');
        $importer = new BatchImporter(filePath: $csvFile);
        $importer->setTableInfo(tableName: 'zones', rowMapping: [
            'name' => 1,
            'zone_code' => 2,
        ]);
        $importer->setRowTransformer(function (array $record): array {
            $now = now();
            $record['created_at'] = $now;
            $record['updated_at'] = $now;

            return $record;
        });
        // name,zone_code
        $importer->import(1);
    }

    public function importCountries(): void
    {
        /** @var array<string, int> $zoneIds */
        $zoneIds = Zone::query()->pluck('id', 'zone_code')->all();

        $csvFile = module_path('Brokers', 'database/seeders/csv/default/countries.csv');
        $importer = new BatchImporter(filePath: $csvFile);
        $importer->setTableInfo(tableName: 'countries', rowMapping: [
            'name' => 1,
            'country_code' => 2,
            'zone_code' => 3,
        ]);
        $importer->setRowTransformer(function (array $record) use ($zoneIds): array {
            $zoneCode = $record['zone_code'] ?? null;
            unset($record['zone_code']);

            if ($zoneCode === null || ! isset($zoneIds[$zoneCode])) {
                throw new RuntimeException("Unknown zone_code [{$zoneCode}] for country [{$record['name']}].");
            }

            $record['zone_id'] = $zoneIds[$zoneCode];
            $now = now();
            $record['created_at'] = $now;
            $record['updated_at'] = $now;

            return $record;
        });
        // name,country_code,zone_code
        $importer->import(1);
    }
}
