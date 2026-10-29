<?php

namespace Modules\GeneralSetting\Services;

use DateTimeZone;
use Illuminate\Support\Facades\Log;
use Modules\GeneralSetting\Repositories\SettingRepository;

class SettingService
{
    protected SettingRepository $repo;

    public function __construct(SettingRepository $repo)
    {
        $this->repo = $repo;
    }

    public function get(string $key, $default = null)
    {
        return $this->repo->get($key, $default);
    }

    public function set(string $key, $value)
    {
        return $this->repo->set($key, $value);
    }

    /**
     * Return a validated timezone identifier or null
     * Normalizes common formats: JSON-encoded string, quoted string, etc.
     */
    public function getValidatedTimezone(string $key = 'general.timezone') : ?string
    {
        $raw = $this->get($key);

        if (empty($raw)) {
            return null;
        }

        // If stored as array structure, try to extract string
        if (is_array($raw) && isset($raw[0]) && is_string($raw[0])) {
            $tz = $raw[0];
        } else {
            $tz = $raw;
        }

        // If it's JSON encoded string like '"Asia\/Kolkata"' or similar
        if (is_string($tz)) {
            $decoded = @json_decode($tz, true);
            if (json_last_error() === JSON_ERROR_NONE && is_string($decoded)) {
                $tz = $decoded;
            }
            $tz = trim($tz, "\"' ");
        }

        // Finally make sure it's a known timezone identifier
        $identifiers = DateTimeZone::listIdentifiers();

        if (is_string($tz) && in_array($tz, $identifiers, true)) {
            return $tz;
        }

        // Not valid
        Log::warning("Invalid timezone setting stored for {$key}: " . var_export($raw, true));
        return null;
    }
}
