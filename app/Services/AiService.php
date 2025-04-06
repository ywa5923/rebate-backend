<?php

namespace App\Services;

use App\Contracts\AiClientInterface;
use App\Utilities\AiTransaltionType;
class AiService
{
    public function __construct(protected AiClientInterface $aiClient){}
   
    public function translateJson(string $jsonString, string $targetLanguage): string
    {
        return $this->aiClient->translate($jsonString, $targetLanguage, AiTransaltionType::JSON);
    }

    public function translateText(string $text, string $targetLanguage): string
    {
        return $this->aiClient->translate($text, $targetLanguage, AiTransaltionType::TEXT);
    }
}   
