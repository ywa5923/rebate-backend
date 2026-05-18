<?php

namespace Modules\Brokers\Database\Seeders;

use Database\Seeders\BatchImporter;
use Illuminate\Database\Seeder;

class RegulatorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->importRegulators();
    }

    public function importRegulators(): void
    {
        $csvFile = module_path('Brokers', 'database/seeders/csv/default/regulators.csv');
        $importer = new BatchImporter(filePath: $csvFile);
        $importer->setTableInfo(tableName: 'regulators', rowMapping: [
            'name' => 1,
            'acronym' => 2,
            'country' => 3,
            'zone' => 4,
            'tier_classification' => 5,
            'rating' => 6,
            'investor_protection_scheme' => 7,
            'compensation_scheme' => 8,
            'retail_leverage_restrictions' => 9,
            'notes' => 10,
            'website' => 11,
            'year_established' => 12,
            'jurisdiction_type' => 13,
            'description' => 14,
            'is_invariant' => -1,
        ]);
        $importer->setRowTransformer(function (array $record, array $csvRow): array {
            $record['rating'] = self::normalizeRating($record['rating'] ?? null);
            $record['year_established'] = self::normalizeYear($record['year_established'] ?? null);
            $now = now();
            $record['created_at'] = $now;
            $record['updated_at'] = $now;

            return $record;
        });
        // Regulator name,Acronym,Country,Zone,Tier classification,Rating,Investor protection scheme,Compensation limits,Retail leverage restrictions (typical),Notes,Regulator website URL,Year established,Regulatory jurisdiction type,Regulator description
        $importer->import(1);
    }

    public static function normalizeRating(mixed $rating): ?string
    {
        if ($rating === null || $rating === '' || $rating === 'N/A') {
            return null;
        }

        if (is_numeric($rating)) {
            return number_format((float) $rating, 2, '.', '');
        }

        if (preg_match('/(\d+(?:\.\d+)?)/', (string) $rating, $matches)) {
            return number_format((float) $matches[1], 2, '.', '');
        }

        return null;
    }

    public static function normalizeYear(mixed $year): ?int
    {
        if ($year === null || $year === '' || $year === 'N/A') {
            return null;
        }

        if (is_numeric($year)) {
            return (int) $year;
        }

        if (preg_match('/(\d{4})/', (string) $year, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}
