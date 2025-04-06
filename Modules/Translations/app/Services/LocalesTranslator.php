<?php
namespace Modules\Translations\Services;

use App\Services\AiService;
use Illuminate\Support\Facades\File;
use Modules\Translations\Utilities\JsonTranslationImporter;

class LocalesTranslator
{
    private string $sourceLanguage = 'en';
    private array $results = [];
    private array $errors = [];
    private string $targetLanguage;
    private string $target_locale_code;
    private string $sourcePath;
    private string $targetPath;

    public function __construct(protected AiService $aiService,protected JsonTranslationImporter $importer){}


    public function translateLocales(string $targetLanguage,string $target_locale_code): array
    {
        $modulePath = module_path('Translations');
        $this->targetLanguage = $targetLanguage;
        $this->target_locale_code = $target_locale_code;
        $this->sourcePath = $modulePath . '/database/seeders/locales/' . $this->sourceLanguage;
        $this->targetPath = $modulePath . '/database/seeders/locales/' .  $this->target_locale_code;

        try {
            // Create target directory if it doesn't exist
            if (!File::exists($this->targetPath)) {
                File::makeDirectory($this->targetPath, 0755, true);
            }

            // Get all JSON files from source directory
            $files = File::glob($this->sourcePath . '/*.json');

            if (empty($files)) {
                $this->errors[] = "No JSON files found in source directory: {$this->sourcePath}";
                return $this->getResponse();
            }

            foreach ($files as $file) {
                $this->translateAndSaveFile($file);
            }

           
            return $this->getResponse();
        } catch (\Exception $e) {
            $this->errors[] = "General error: " . $e->getMessage();
            return $this->getResponse();
        }
    }
     /**
     * Get the final response array
     */
    private function getResponse(): array
    {
        return [
            'success' => empty($this->errors),
            'total_files' => count($this->results),
            'processed_files' => $this->results,
            'errors' => $this->errors
        ];
    }

    /**
     * Translate a single JSON file and save it to the target directory
     */
    private function translateAndSaveFile(string $filePath): void
    {
        $fileName = basename($filePath);
        // Extract the base name without extension
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        // Replace last occurrence of source language code with target locale code
        $newFileName = substr_replace($baseName, $this->target_locale_code, strrpos($baseName, $this->sourceLanguage), strlen($this->sourceLanguage)) . '.json';
        
        $content = json_decode(File::get($filePath), true);
        
        if (!is_array($content)) {
            $this->errors[] = "Invalid JSON in file: {$fileName}";
            return;
        }

        try {
            // Convert the entire JSON content to a string for translation
            $jsonString = json_encode($content, JSON_UNESCAPED_UNICODE);
            
            $translatedJson = $this->AI_translateJson($jsonString);
            $translatedContent = json_decode($translatedJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Failed to parse translated JSON: " . json_last_error_msg());
            }

            // Save translated content with new filename
            $targetFile = $this->targetPath . '/' . $newFileName;
            File::put($targetFile, json_encode($translatedContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // Import the translated JSON file into the database
            $this->importer->importJson($targetFile);
            
            $this->results[] = [
                'file' => $newFileName,
                'status' => 'success',
                'imported' => true,
                'source_path' => $filePath,
                'target_path' => $targetFile
            ];
        } catch (\Exception $e) {
            $this->errors[] = "Error processing file {$fileName}: " . $e->getMessage();
            $this->results[] = [
                'file' => $fileName,
                'status' => 'error',
                'imported' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Translate the entire JSON content using ChatGPT
     */
    private function AI_translateJson(string $jsonString): string
    {
        return $this->aiService->translateJson($jsonString, $this->targetLanguage);
    }


}