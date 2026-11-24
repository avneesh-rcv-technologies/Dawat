<?php

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Activitylog\Exceptions\ApiExceptionHandler;
use Modules\Filemanager\Exceptions\FileServiceException;
use Modules\Filemanager\Services\FileService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

if (!function_exists('fn_get_image')) {
    function fn_get_image($object_type, $object_id = 0)
    {
        try {
            $fileservice = app(FileService::class);
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
            $fileservice = app(FileService::class);
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
            $fileservice = app(FileService::class);
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
        return ['public', 's3'];
    }
}

if (!function_exists('fn_max_upload_megabytes')) {
    function fn_max_upload_megabytes(): int
    {
        return max(1, (int) (fn_get_setting('general.filemanager.max_file_size') ?? 10));
    }
}

if (!function_exists('fn_max_upload_kilobytes')) {
    /**
     * Max upload size in KB from Filemanager settings (for Laravel max: rules).
     */
    function fn_max_upload_kilobytes(): int
    {
        return fn_max_upload_megabytes() * 1024;
    }
}

if (!function_exists('fn_allowed_image_mimes')) {
    /**
     * Image mime list intersected with Filemanager allowed extensions.
     *
     * @return list<string>
     */
    function fn_allowed_image_mimes(): array
    {
        $defaults = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'bmp'];
        $configured = array_values(array_filter(array_map(
            static fn ($value) => strtolower(trim((string) $value)),
            explode(',', (string) fn_get_setting('general.filemanager.extensions', ''))
        )));

        if ($configured === []) {
            return $defaults;
        }

        $allowed = array_values(array_intersect($defaults, $configured));

        return $allowed !== [] ? $allowed : $defaults;
    }
}

if (!function_exists('fn_image_validation_rules')) {
    /**
     * Shared Laravel validation rules for image uploads.
     *
     * @return list<string>
     */
    function fn_image_validation_rules(bool $required = false): array
    {
        $allowed = fn_allowed_image_mimes();
        $mimes = implode(',', $allowed);
        // Laravel's `image` rule excludes SVG unless allow_svg is passed.
        $imageRule = in_array('svg', $allowed, true) ? 'image:allow_svg' : 'image';

        return [
            $required ? 'required' : 'nullable',
            $imageRule,
            'mimes:' . $mimes,
            'max:' . fn_max_upload_kilobytes(),
        ];
    }
}

if (!function_exists('fn_image_validation_messages')) {
    /**
     * User-facing validation messages for an image field.
     *
     * @return array<string, string>
     */
    function fn_image_validation_messages(string $field = 'image'): array
    {
        $maxMb = fn_max_upload_megabytes();
        $mimes = implode(', ', fn_allowed_image_mimes());
        $label = str_replace('_', ' ', $field);
        $article = preg_match('/^[aeiou]/i', $label) ? 'an' : 'a';

        return [
            "{$field}.required" => "Please choose {$article} {$label}.",
            "{$field}.image" => "The {$label} must be a valid image file.",
            "{$field}.mimes" => "The {$label} must be a file of type: {$mimes}.",
            "{$field}.max" => "The {$label} must not be greater than {$maxMb} MB.",
            "{$field}.uploaded" => "The {$label} failed to upload. It may exceed the PHP upload limit "
                . '(upload_max_filesize=' . ini_get('upload_max_filesize')
                . ', post_max_size=' . ini_get('post_max_size') . ').',
        ];
    }
}

if (!function_exists('fn_assert_uploaded_file')) {
    /**
     * Ensure the uploaded file is present and accepted by PHP.
     *
     * @throws ValidationException
     */
    function fn_assert_uploaded_file($file, string $field = 'file'): UploadedFile
    {
        if (! $file instanceof UploadedFile) {
            throw ValidationException::withMessages([
                $field => "The {$field} upload is missing or invalid.",
            ]);
        }

        if (! $file->isValid()) {
            $uploadMax = ini_get('upload_max_filesize');
            $postMax = ini_get('post_max_size');

            throw ValidationException::withMessages([
                $field => "The {$field} failed to upload. It may exceed the PHP upload limit "
                    . "(upload_max_filesize={$uploadMax}, post_max_size={$postMax}).",
            ]);
        }

        return $file;
    }
}

if (!function_exists('fn_upload_image')) {
    function fn_upload_image($file, $object, $object_id = 0, $is_remove_existing = false, string $field = 'file')
    {
        $file = fn_assert_uploaded_file($file, $field);

        $fileService = app(FileService::class);

        try {
            if ($is_remove_existing) {
                // Validate first: a rejected upload must not delete the current file.
                $fileService->assertValidUpload($file);
                $fileService->deleteFile($object, $object_id);
            }

            return $fileService->uploadFile(
                $file,
                $object,
                $object_id
            );
        } catch (FileServiceException $e) {
            throw ValidationException::withMessages([
                $field => $e->getMessage(),
            ]);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                $field => $e->getMessage(),
            ]);
        }
    }
}

if (!function_exists('fn_upload_request_file')) {
    /**
     * Upload a request file field via Filemanager.
     * Returns null when the field was not submitted.
     * Throws ValidationException when a file was submitted but rejected.
     *
     * Prefer this over hasFile() + fn_upload_image() so oversized uploads are not silently skipped.
     */
    function fn_upload_request_file(
        Request $request,
        string $field,
        string $object,
        $object_id = 0,
        bool $is_remove_existing = false
    ) {
        $file = $request->file($field);

        if (! $file) {
            return null;
        }

        return fn_upload_image($file, $object, $object_id, $is_remove_existing, $field);
    }
}
