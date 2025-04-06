<?php
namespace Modules\Translations\Utilities;
use Illuminate\Support\Facades\File;
use Modules\Translations\Models\LocaleResource;
use Modules\Translations\Models\Translation;
class JsonTranslationImporter
{
    public function __construct(){}

    public function importDirectory(string $directoryPath): void
    {
        $jsonFiles = File::glob($directoryPath . '/*.json');
        foreach($jsonFiles as $jsonFile)
        {
            $this->importJson($jsonFile);
        }
    }

    public function importJson(string $filePath): void
    {
        $latestDash = strrpos($filePath, '_');
        $latestDot = strrpos($filePath, '.');
        $latestSlash = strrpos($filePath, "/");

        $key = substr($filePath, $latestSlash + 1, $latestDash - $latestSlash - 1);
        $localeInfo = substr($filePath, $latestDash + 1, $latestDot - $latestDash - 1);
        [$locale, $zone] = explode("-", $localeInfo);

        if(empty($key) || empty($locale) || empty($zone))
        {
            throw new \Exception("Invalid file name: " . $filePath);
        }

        // Read and decode JSON file
        $jsonContent = json_decode(file_get_contents($filePath), true);

        if (!$jsonContent) {
            throw new \Exception("Invalid JSON content in: " . $filePath);
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