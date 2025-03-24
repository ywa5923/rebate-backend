<?php

namespace Modules\Translations\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;
use App\Services\OpenAiService;

class LocaleResource2Seeder extends Seeder
{
    
    private string $sourcePath;
    private string $targetPath;
    private string $sourceLanguage = 'en';
    private string $targetLanguage;
    private string $target_locale_code;
    private array $results = [];
    private array $errors = [];

    public function __construct(string $targetLanguage,string $target_locale_code)
    {
       
        $modulePath = module_path('Translations');
        $this->target_locale_code = $target_locale_code;
        $this->targetLanguage = $targetLanguage;
        $this->sourcePath = $modulePath . '/database/seeders/locales/' . $this->sourceLanguage;
        $this->targetPath = $modulePath . '/database/seeders/locales/' .  $this->target_locale_code;
    }

    /**
     * Run the translation process and return results
     */
    public function run(): array
    {
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
            
            $translatedJson = $this->translateJson($jsonString);
            $translatedContent = json_decode($translatedJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Failed to parse translated JSON: " . json_last_error_msg());
            }

            // Save translated content with new filename
            $targetFile = $this->targetPath . '/' . $newFileName;
            File::put($targetFile, json_encode($translatedContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            $this->results[] = [
                'file' => $newFileName,
                'status' => 'success',
                'source_path' => $filePath,
                'target_path' => $targetFile
            ];
        } catch (\Exception $e) {
            $this->errors[] = "Error processing file {$fileName}: " . $e->getMessage();
            $this->results[] = [
                'file' => $fileName,
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Translate the entire JSON content using ChatGPT
     */
    private function translateJson(string $jsonString): string
    {
        $openAiService = App::make(OpenAiService::class);
        return $openAiService->translateJson($jsonString, $this->targetLanguage);
    }

    private function translateJson2(string $jsonString): string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ', //$this->openaiApiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "You are a professional translator. Translate the following JSON content to Romanian. 
                        IMPORTANT: 
                        1. Only translate the VALUES, not the KEYS
                        2. Maintain the exact same JSON structure
                        3. Keep all special characters and formatting
                        4. Return ONLY the translated JSON, no explanations
                        5. Ensure the response is valid JSON"
                    ],
                    [
                        'role' => 'user',
                        'content' => $jsonString
                    ]
                ],
                'temperature' => 0.3,
                'max_tokens' => 4000
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return trim($result['choices'][0]['message']['content']);
            }

            throw new \Exception("API request failed: " . $response->body());

        } catch (\Exception $e) {
            throw new \Exception("Translation error: " . $e->getMessage());
        }
    }
}
