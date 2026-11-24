<?php

namespace Modules\FoodManagements\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FoodImageUploadPolicy
{
    public const MAX_IMAGES = 10;

    public const MAX_SIZE_KB = 2048;

    public const EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    public static function fileRules(): array
    {
        $extensions = implode(',', self::EXTENSIONS);

        return ['bail', 'required', 'file', 'image', 'max:'.self::MAX_SIZE_KB, 'mimes:'.$extensions, 'extensions:'.$extensions];
    }

    public static function messages(string $field): array
    {
        return [
            "$field.required" => 'Please upload at least one food image.',
            "$field.array" => 'Please select your food images as a list of files.',
            "$field.list" => 'Please select your food images as a list of files.',
            "$field.max" => 'A food item can have at most 10 images.',
            "$field.*.required" => 'Please select a valid food image.',
            "$field.*.file" => 'Please select a valid food image file.',
            "$field.*.image" => 'Only JPG, JPEG, PNG, and WebP images are allowed.',
            "$field.*.mimes" => 'Only JPG, JPEG, PNG, and WebP images are allowed.',
            "$field.*.extensions" => 'Only .jpg, .jpeg, .png, and .webp image files are allowed.',
            "$field.*.max" => 'Each food image must be 2 MB or smaller.',
        ];
    }

    public static function validateFiles(array $images, string $field = 'food_image'): void
    {
        Validator::make([$field => $images], [
            $field => ['array', 'list', 'max:'.self::MAX_IMAGES],
            "$field.*" => self::fileRules(),
        ], self::messages($field))->validate();
    }

    public static function assertCount(int $existing, int $incoming, string $field = 'images'): void
    {
        if ($existing + $incoming > self::MAX_IMAGES) {
            $remaining = max(0, self::MAX_IMAGES - $existing);
            throw ValidationException::withMessages([
                $field => "A food item can have at most 10 images. You already have $existing images and can add $remaining more. Delete an image or replace the gallery first.",
            ]);
        }
    }
}
