<?php

namespace App\Contracts;

use Illuminate\Http\UploadedFile;

interface StorageClientInterface
{
    public function uploadFile(UploadedFile $file, string $path): string;
    public function uploadFromString(string $content, string $key, string $contentType = 'application/octet-stream'): string;
    public function deleteFile(string $key): bool;
    public function getUrl(string $key): string;
} 