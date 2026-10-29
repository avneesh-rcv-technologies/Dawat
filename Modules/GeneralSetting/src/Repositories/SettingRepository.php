<?php

namespace Modules\GeneralSetting\Repositories;

use Modules\GeneralSetting\Models\Setting;

class SettingRepository
{
    /**
     * Get a setting value by key (dot notation).
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $record = Setting::where('key', $key)->first();

        if (!$record) {
            return $default;
        }

        // value cast is array in the model; but sometimes value may be stored as raw JSON/string
        $value = $record->value;

        // If value is a single string wrapped in quotes (seeded), try to normalize
        if (is_string($value)) {
            $decoded = @json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && $decoded !== null) {
                $value = $decoded;
            }
        }

        return $value;
    }

    /**
     * Set a setting value by key (will create or update).
     *
     * @param string $key
     * @param mixed $value
     * @return Setting
     */
    public function set(string $key, $value)
    {
        $record = Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        return $record;
    }

    /**
     * Get all settings as key => value
     *
     * @return array
     */
    public function all(): array
    {
        return Setting::all()->pluck('value', 'key')->toArray();
    }

    /**
     * Delete a setting by key.
     */
    public function delete(string $key): void
    {
        Setting::where('key', $key)->delete();
    }
}
