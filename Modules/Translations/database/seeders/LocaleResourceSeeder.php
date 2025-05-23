<?php

namespace Modules\Translations\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Translations\Models\LocaleResource;
use Modules\Translations\Models\Translation;
use Illuminate\Support\Facades\File;
use Modules\Translations\Utilities\JsonTranslationImporter;
class LocaleResourceSeeder extends Seeder
{
    public function __construct(protected JsonTranslationImporter $jsonImporter)
    {
        
    }
    //sectiunile care sunt invariante se adauga zona ca invariant:
    //ex:   'database/seeders/home_page_en-invariant.json',
    //-------------------------------------------------------//
    //se adauga mai intai fisierele in limba engleza si apoi se adauga celelalte locale

   

    public function run()
    {
        $localesPath = module_path('Translations', 'database/seeders/locales');
        
        // Get all JSON files from all locale directories
        $files = [];
        foreach (File::directories($localesPath) as $localeDir) {
            $files = array_merge($files, File::glob($localeDir . '/*.json'));
        }

        // Sort files to ensure English files are processed first
        usort($files, function($a, $b) {
            $isEnA = strpos($a, '_en-') !== false;
            $isEnB = strpos($b, '_en-') !== false;
            return $isEnB - $isEnA;
        });

        foreach($files as $file)
        {
           // $this->insertJsonFile($file);
                $this->jsonImporter->importJson($file);
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
