<?php

namespace Modules\Filemanager\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Modules\Filemanager\Contracts\FileStorageDriverInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PublicFileStorageService implements FileStorageDriverInterface
{
    public function getDriver(): string
    {
        return 'public';
    }

    public function put(string $path, string $contents): void
    {
        $this->disk()->put($path, $contents);
    }

    public function putFileAs(string $directory, UploadedFile $file, string $fileName): void
    {
        $this->disk()->putFileAs($directory, $file, $fileName);
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
        return $this->disk()->url($path);
    }

    public function makeDirectory(string $path): void
    {
        $this->disk()->makeDirectory($path);
    }

    protected function disk(): Filesystem
    {
        return Storage::disk('public');
    }
}
