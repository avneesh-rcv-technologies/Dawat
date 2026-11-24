<?php

namespace Modules\Filemanager\Contracts;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileStorageDriverInterface
{
    public function getDriver(): string;

    public function put(string $path, string $contents): void;

    public function putFileAs(string $directory, UploadedFile $file, string $fileName): void;

    public function delete(string|array $paths): void;

    public function exists(string $path): bool;

    public function url(string $path): string;

    public function makeDirectory(string $path): void;
}
