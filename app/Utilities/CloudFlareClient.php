<?php

namespace App\Utilities;

use Aws\S3\S3Client;
use Illuminate\Http\UploadedFile;
use App\Contracts\StorageClientInterface;

class CloudFlareClient implements StorageClientInterface
{
    private string $bucket;

    public function __construct(
        private readonly S3Client $client
    ) {
        $this->bucket = config('services.cloudflare.bucket');
    }

    /**
     * Upload a file to Cloudflare R2
     */
    public function uploadFile(UploadedFile $file, string $path): string
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $randomNumbers = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $key = $path . '/' . $originalName . '_' . $randomNumbers . '.' . $extension;
        
        $this->client->putObject([
            'Bucket' => $this->bucket,
            'Key'    => $key,
            'Body'   => fopen($file->getPathname(), 'r'),
            'ContentType' => $file->getMimeType(),
        ]);

        return $key;
    }

    /**
     * Upload a file from a string
     */
    public function uploadFromString(string $content, string $key, string $contentType = 'application/octet-stream'): string
    {
        $this->client->putObject([
            'Bucket' => $this->bucket,
            'Key'    => $key,
            'Body'   => $content,
            'ContentType' => $contentType,
        ]);

        return $key;
    }

    /**
     * Delete a file from Cloudflare R2
     */
    public function deleteFile(string $key): bool
    {
        try {
            $this->client->deleteObject([
                'Bucket' => $this->bucket,
                'Key'    => $key,
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the public URL for a file
     */
    public function getUrl(string $key): string
    {
        return config('services.cloudflare.public_url') . '/' . $key;
    }
}
