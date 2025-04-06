<?php

namespace App\Utilities;

use App\Contracts\AiClientInterface;
use OpenAI\Client;
use App\Utilities\AiTransaltionType;
class OpenAiClient implements AiClientInterface
{
    public function __construct(protected Client $openAiClient){}

    public function translate(string $content, string $targetLanguage, AiTransaltionType $type ): string
    {
        return match($type)
        {
            AiTransaltionType::JSON => $this->translateJson($content, $targetLanguage),
            AiTransaltionType::TEXT => $this->translateText($content, $targetLanguage),
        };   
    }

    public function translateJson(string $jsonString, string $targetLanguage): string
    {
        $response = $this->openAiClient->chat()->create([
            'model' => 'gpt-3.5-turbo',//'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "You are a professional translator. Translate the following JSON content to a language where country is {$targetLanguage}. 
                    IMPORTANT: 
                    1. Only translate the VALUES, not the KEYS
                    2. Maintain the exact same JSON structure
                    3. Keep all special characters and formatting
                    4. Return ONLY the translated JSON, no explanations
                    5. Ensure the response is valid JSON"
                ],
                ['role' => 'user', 'content' =>$jsonString]
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.3,
            'max_tokens' => 4000
        ]);
        return $response->choices[0]->message->content??'No response from OpenAI';
    }

    public function translateText(string $text, string $targetLanguage): string
    {
        $response = $this->openAiClient->chat()->create([
            'model' => 'gpt-3.5-turbo',//'gpt-4o-mini',
            'messages' => [
                ['role' => 'user', 'content' => $text]
            ],
            'temperature' => 0.3,
            'max_tokens' => 4000
        ]);
        return $response->choices[0]->message->content??'No response from OpenAI';
    }
}