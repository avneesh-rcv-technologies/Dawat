<?php

namespace Modules\Activitylog\Services;

use Modules\Activitylog\Events\ActivityLogged;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Modules\Activitylog\Models\Activity;

class ActivityLoggerService
{
    public static function log(string $description, ?Model $subject = null, array $properties = [], $guard = 'admin'): Activity
    {
        $defaultProperties = [
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'location' => self::getLocationFromIP(Request::ip()),
            'severity' => 'info',
            'category' => 'general'
        ];

        $properties = array_merge($defaultProperties, $properties);

        // Get the authenticated user from the appropriate guard
        $causer = null;
        $causerId = null;
        $causerType = null;

        if (Auth::guard($guard)->check()) {
            $causer = Auth::guard($guard)->user();
            $causerId = $causer->id;
            $causerType = get_class($causer);
        } elseif (Auth::check()) {
            $causer = Auth::user();
            $causerId = $causer->id;
            $causerType = get_class($causer);
        }

        $activityData = [
            'log_name' => $properties['category'] ?? 'default',
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->getKey() : null,
            'causer_type' => $causerType,
            'causer_id' => $causerId,
            'properties' => $properties,
            'event' => $properties['event'] ?? null,
        ];

        $activity = Activity::create($activityData);
        event(new ActivityLogged($activity));
        return $activity;
    }

    public static function logWithSeverity(string $description, string $severity, ?Model $subject = null, array $properties = []): Activity
    {
        $properties['severity'] = $severity;
        return self::log($description, $subject, $properties);
    }

    public static function logWithCategory(string $description, string $category, ?Model $subject = null, array $properties = []): Activity
    {
        $properties['category'] = $category;
        return self::log($description, $subject, $properties);
    }

    public static function logCritical(string $description, ?Model $subject = null, array $properties = []): Activity
    {
        return self::logWithSeverity($description, 'critical', $subject, $properties);
    }

    public static function logWarning(string $description, ?Model $subject = null, array $properties = []): Activity
    {
        return self::logWithSeverity($description, 'warning', $subject, $properties);
    }

    public static function logInfo(string $description, ?Model $subject = null, array $properties = []): Activity
    {
        return self::logWithSeverity($description, 'info', $subject, $properties);
    }

    public static function logAuthentication(string $description, array $properties = []): Activity
    {
        return self::logWithCategory($description, 'authentication', null, $properties);
    }

    public static function logUserManagement(string $description, ?Model $subject = null, array $properties = []): Activity
    {
        return self::logWithCategory($description, 'user_management', $subject, $properties);
    }

    public static function logContent(string $description, ?Model $subject = null, array $properties = []): Activity
    {
        return self::logWithCategory($description, 'content', $subject, $properties);
    }

    public static function logSystem(string $description, array $properties = []): Activity
    {
        return self::logWithCategory($description, 'system', null, $properties);
    }

    protected static function getLocationFromIP(string $ip): string
    {
        $locations = [
            '127.0.0.1' => 'N/A',
            '192.168.' => 'Local Network',
            '10.0.' => 'Private Network',
        ];

        foreach ($locations as $prefix => $location) {
            if (str_starts_with($ip, $prefix)) {
                return $location;
            }
        }

        return 'N/A';
    }
}
