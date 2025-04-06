<?php

namespace App\Contracts;

use App\Utilities\AiTransaltionType;

interface AiClientInterface
{
    public function translate(string $content, string $targetLanguage, AiTransaltionType $type ): string;
}
