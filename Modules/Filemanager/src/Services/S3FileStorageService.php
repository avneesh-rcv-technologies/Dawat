<?php

namespace Modules\Filemanager\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Modules\Filemanager\Contracts\FileStorageDriverInterface;
use Modules\Filemanager\Exceptions\FileServiceException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class S3FileStorageService implements FileStorageDriverInterface
{
    protected ?Filesystem $disk = null;

    public function getDriver(): string
    {
        return 's3';
    }

    public function put(string $path, string $contents): void
    {
        $this->disk()->put($path, $contents);
    }

    public function putFileAs(
        string $directory,
        UploadedFile $file,
        string $fileName
    ): void {
        $this->disk()->put(
            $directory . '/' . $fileName,
            file_get_contents($file->getRealPath())
    );
}
    public function delete(string|array $paths): void
    {
        $this->disk()->delete($paths);
    }

    public function exists(string $path): bool
    {
        return $this->disk()->exists($path);
    }

    public function url(string $path): string
    {
        $path = ltrim($path, '/');

        return rtrim($this->publicBaseUrl(), '/') . '/' . $path;
    }

    protected function publicBaseUrl(): string
    {
        $configuredUrl = config('filesystems.disks.s3.url');

        if (filled($configuredUrl)) {
            return rtrim((string) $configuredUrl, '/');
        }

        $bucket = $this->bucket();

        if (str_contains($bucket, '.')) {
            return 'https://' . $bucket;
        }

        $region = (string) (config('filesystems.disks.s3.region') ?: 'us-east-1');

        return "https://{$bucket}.s3.{$region}.amazonaws.com";
    }

    public function makeDirectory(string $path): void
    {
        $this->disk()->makeDirectory($path);
    }

    protected function disk(): Filesystem
    {
        if ($this->disk instanceof Filesystem) {
            return $this->disk;
        }

        // Use the configured Laravel S3 disk.
        $this->disk = Storage::disk('s3');

        return $this->disk;
    }

    protected function bucket(): string
    {
        $bucket = (string) fn_get_setting(
            'filesystems.disks.s3.bucket',
            config('filesystems.disks.s3.bucket')
        );

        if ($bucket === '') {
            throw new FileServiceException(
                'AWS S3 bucket is missing. Set the bucket name in Filemanager settings.'
            );
        }

        return $bucket;
    }
}
