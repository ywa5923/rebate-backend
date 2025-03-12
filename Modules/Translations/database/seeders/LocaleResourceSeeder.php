<?php

namespace Modules\Translations\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Translations\Models\LocaleResource;
use Modules\Translations\Models\Translation;

class LocaleResourceSeeder extends Seeder
{
    //sectiunile care sunt invariante se adauga zona ca invariant:
    //ex:   'database/seeders/home_page_en-invariant.json',
    //-------------------------------------------------------//
    //se adauga mai intai fisierele in limba engleza 

    public $files=[
        'database/seeders/home_page_en-eu.json',
        'database/seeders/home_page_en-sua.json',
        'database/seeders/home_page_en-invariant.json',
        'database/seeders/home_page_ro-eu.json',
        'database/seeders/home_page_ro-sua.json',
        'database/seeders/home_page_ro-invariant.json',
    ];

    public function run()
    {
        foreach($this->files as $file)
        {
            $fileName = module_path('Translations', $file);
            $this->insertJsonFile($fileName);
        }
     
    }

    public function insertJsonFile($fileName)
    {
      
        $latestDash = strrpos($fileName, '_');
        $latestDot = strrpos($fileName, '.');
        $latestSlash = strrpos($fileName, "/");

        $key = substr($fileName, $latestSlash + 1, $latestDash - $latestSlash - 1);
        $localeInfo = substr($fileName, $latestDash + 1, $latestDot - $latestDash - 1);
        [$locale, $zone] = explode("-", $localeInfo);

        if(empty($key) || empty($locale) || empty($zone))
        {
            throw new \Exception("Invalid file name: " . $fileName);
        }

        // Read and decode JSON file
        $jsonContent = json_decode(file_get_contents($fileName), true);

        if (!$jsonContent) {
            throw new \Exception("Invalid JSON content in: " . $fileName);
        }

        // Insert into LocaleResource or Translation
        if ($locale === "en") {
            $this->insertEnglishLocale($jsonContent, $key, $zone);
        } else {
            $this->insertTranslatedLocale($jsonContent, $key, $zone, $locale);
        }

    }

    /**
     * Insert English locale resources.
     */
    function insertEnglishLocale(array $jsonArray, string $key, string $zone): void
    {

        foreach ($jsonArray as $section => $content) {
            LocaleResource::insert([
                "key" => $key,
                "section" => $section,
                "zone_code" => $zone,
                "is_invariant"=>$zone==="invariant"?1:0,
                "json_content" => json_encode($content)
            ]);
        }
    }

    /**
     * Insert translated locale resources.
     */
    function insertTranslatedLocale(array $jsonArray, string $key, string $zone, string $locale): void
    {
        foreach ($jsonArray as $section => $content) {
            $enResource = LocaleResource::where([
                "key" => $key,
                "section" => $section,
                "zone_code" => $zone,
                "is_invariant"=>($zone==="invariant")?1:0
            ])->first();

            if (!$enResource) {
                throw new \Exception("No matching English resource found for section: $section");
            }

            Translation::insert([
                "translationable_type" => LocaleResource::class,
                "translationable_id" => $enResource->id,
                "language_code" => $locale,
                "translation_type" => "property",
                "property" => "json_content",
                "value" => json_encode($content)
            ]);
        }
    }

   
}
