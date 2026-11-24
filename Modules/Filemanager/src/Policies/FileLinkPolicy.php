<?php

namespace Modules\Filemanager\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;
use Modules\Acl\Models\Client;
use Modules\Filemanager\Models\FileLink;
use Modules\Filemanager\Services\FileService;

class FileLinkPolicy
{
    use HandlesAuthorization;

    public function delete(mixed $user, FileLink $fileLink): bool
    {
        $guard = Auth::getDefaultDriver();

        return app(FileService::class)->userCanDeleteFileLink($fileLink, $user, $guard);
    }
}
