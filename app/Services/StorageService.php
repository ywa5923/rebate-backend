<?php

namespace App\Services;

use App\Contracts\StorageClientInterface;
use Illuminate\Http\UploadedFile;

class StorageService
{
    public function __construct(
        protected StorageClientInterface $storageClient
    ) {}

    public function uploadFile(UploadedFile $file, string $path): string
    {
        return $this->storageClient->uploadFile($file, $path);
    }

    public function uploadFromString(string $content, string $key, string $contentType = 'application/octet-stream'): string
    {
        return $this->storageClient->uploadFromString($content, $key, $contentType);
    }

    public function deleteFile(string $key): bool
    {
        return $this->storageClient->deleteFile($key);
    }

    public function getUrl(string $key): string
    {
        return $this->storageClient->getUrl($key);
    }
} 