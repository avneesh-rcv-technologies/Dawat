<?php

use Modules\Filemanager\Services\FileService;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;

if (!function_exists('fn_get_image')) {
    function fn_get_image($object_type, $object_id = 0)
    {
        try {
            $fileservice = new FileService();
            return $fileservice->getFile($object_type, $object_id);
        } catch (Exception $e) {
            ApiExceptionHandler::handle($e);
            return false;
        }
    }
}

if (!function_exists('fn_get_default_image')) {
    function fn_get_default_image()
    {
        try {
            $fileservice = new FileService();
            return $fileservice->getDefaultImage();
        } catch (Exception $e) {
            ApiExceptionHandler::handle($e);
            return false;
        }
    }
}

if (!function_exists('fn_get_images')) {
    function fn_get_images($object_type, $object_id = 0)
    {
        try {
            $fileservice = new FileService();
            return $fileservice->getFiles($object_type, $object_id);
        } catch (Exception $e) {
            ApiExceptionHandler::handle($e);
            return false;
        }
    }
}

if (!function_exists('fn_get_upload_driver')) {
    function fn_get_upload_driver()
    {
        return ['public'];
    }
}

if (!function_exists('fn_upload_image')) {
    function fn_upload_image($file, $object, $object_id = 0, $is_remove_existing = false)
    {
        $fileService = new FileService();

        if ($is_remove_existing) {
            $fileService->deleteFile($object, $object_id);
        }


        return $fileService->uploadFile(
            $file,
            $object,
            $object_id
        );
    }
}
