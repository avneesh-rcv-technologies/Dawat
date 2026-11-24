<?php

namespace Modules\Filemanager\Services;

use Illuminate\Support\Facades\DB;
use Modules\Filemanager\Models\File;
use Modules\Filemanager\Models\FileLink;

class FileRecordService
{
    public function createFileWithLink(array $fileData, array $linkData): FileLink
    {
        return DB::transaction(function () use ($fileData, $linkData) {
            $file = File::create($fileData);

            $fileLink = FileLink::create(array_merge($linkData, [
                'file_id' => $file->id,
            ]));

            return $fileLink->load('file');
        });
    }

    public function getObjectFileLinks(string $objectType, $objectId, ?string $type = null)
    {
        $query = FileLink::with('file')
            ->where('object_type', $objectType)
            ->where('object_id', $objectId)
            ->orderBy('position');

        if ($type !== null) {
            $query->where('type', $type);
        }

        return $query->get();
    }

    public function getObjectFileLink(string $objectType, $objectId): ?FileLink
    {
        return FileLink::with('file')
            ->where('object_type', $objectType)
            ->where('object_id', $objectId)
            ->first();
    }

    public function getFileLinkByLinkId(int $fileLinkId): ?FileLink
    {
        return FileLink::with('file')->find($fileLinkId);
    }

    public function getFileById(int $fileId): ?File
    {
        return File::find($fileId);
    }

    public function getFileLinkByFileId(int $fileId): ?FileLink
    {
        return FileLink::where('file_id', $fileId)->first();
    }

    public function getPrivateAvatarLink(int $fileId, string $objectType, int $objectId): ?FileLink
    {
        return FileLink::where('file_id', $fileId)
            ->where('object_type', $objectType)
            ->where('object_id', $objectId)
            ->first();
    }

    public function updateFile(File $file, array $data): void
    {
        $file->update($data);
    }

    public function updateFileLink(FileLink $fileLink, array $data): void
    {
        $fileLink->update($data);
    }

    public function deleteFileLinkAndFile(FileLink $fileLink): void
    {
        DB::transaction(function () use ($fileLink) {
            $file = $fileLink->file;

            $fileLink->delete();

            if ($file && ! FileLink::where('file_id', $file->id)->exists()) {
                $file->delete();
            }
        });
    }

    public function deleteFileAndLinks(File $file): void
    {
        DB::transaction(function () use ($file) {
            FileLink::where('file_id', $file->id)->delete();
            $file->delete();
        });
    }
}
