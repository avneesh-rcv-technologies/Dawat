<?php

namespace Modules\Activitylog\Models;

use Spatie\Activitylog\Models\Activity as SpatieActivity;

class Activity extends SpatieActivity
{
    // Add custom attributes and methods
    protected $appends = [
        'formatted_date',
        'relative_time',
        'full_date',
        'table_date',
        'timeline_time',
        'date_group',
        'user_initials',
        'avatar_color',
        'tags',
        'severity_level',
        'category_name',
        'user_name',
        'user_role',
        'ip_address',
        'location',
        'user_agent',
    ];

    /**
     * Get a property from the properties JSON field
     */
    public function getProperty(string $key, $default = null)
    {
    
      
        $properties = $this->properties ?? [];

        
       
        return data_get($properties, $key, $default);
    }

    /**
     * Set a property in the properties JSON field
     */
    public function setProperty(string $key, $value): void
    {
        $properties = $this->properties ?? [];
        data_set($properties, $key, $value);
        $this->properties = $properties;
    }

    public function getFormattedDateAttribute(): string
    {
        $diffInDays = $this->created_at->diffInDays(now());

        if ($diffInDays == 0) {
            return 'Today at ' . $this->created_at->format('g:i A');
        } elseif ($diffInDays == 1) {
            return 'Yesterday at ' . $this->created_at->format('g:i A');
        } else {
            return $this->created_at->format('M j \a\t g:i A') . " ({$diffInDays} days ago)";
        }
    }

    public function getRelativeTimeAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getFullDateAttribute(): string
    {
        return $this->created_at->format('l, F j, Y \a\t g:i:s A');
    }

    public function getTableDateAttribute(): string
    {
        return $this->created_at->format('M j \a\t g:i A');
    }

    public function getTimelineTimeAttribute(): string
    {
        return $this->created_at->format('g:i A');
    }

    public function getDateGroupAttribute(): string
    {
        return $this->created_at->format('l, F j, Y');
    }

    public function getUserInitialsAttribute(): string
    {
        if (!$this->causer) {
            return 'SY'; // System
        }

        $name = $this->causer->name ?? 'Unknown User';
        $words = explode(' ', $name);

        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }

        return strtoupper(substr($name, 0, 2));
    }

    public function getAvatarColorAttribute(): string
    {
        $colors = [
            'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-red-500',
            'bg-purple-500', 'bg-pink-500', 'bg-indigo-500', 'bg-gray-500'
        ];

        $index = $this->causer_id ? $this->causer_id % count($colors) : 0;
        return $colors[$index];
    }

    public function getTagsAttribute(): array
    {
        $tags = [];

        // Add user role/type
        if ($this->causer) {
            if (method_exists($this->causer, 'getRoleNames')) {
                $roles = $this->causer->getRoleNames();
                if ($roles->isNotEmpty()) {
                    $tags[] = strtolower(str_replace(' ', '_', $roles->first()));
                }
            } else {
                $tags[] = 'user';
            }
        }

        // Add event type
        if ($this->getProperty('event')) {
            $tags[] = $this->getProperty('event');
        }

        // Add severity from properties
        $severity = $this->getProperty('severity', 'medium');
        $tags[] = $severity;

        return $tags;
    }

    public function getSeverityLevelAttribute(): string
    {
        return $this->getProperty('severity', 'medium');
    }

    public function getCategoryNameAttribute(): string
    {
        return $this->getProperty('category', $this->log_name);
    }

    public function getUserNameAttribute(): string
    {
        return $this->causer->name ?? 'System';
    }

    public function getUserRoleAttribute(): string
    {
        if (!$this->causer) {
            return 'System';
        }

        if (method_exists($this->causer, 'getRoleNames')) {
            $roles = $this->causer->getRoleNames();
            return $roles->isNotEmpty() ? $roles->first() : 'User';
        }

        return 'User';
    }

    public function getIpAddressAttribute(): string
    {
        return $this->getProperty('ip_address', request()->ip());
    }

    public function getLocationAttribute(): string
    {
        return $this->getProperty('location', 'N/A');
    }

    public function getUserAgentAttribute(): string
    {
        return $this->getProperty('user_agent', request()->userAgent());
    }

    // Scopes
    public function scopeBySeverity($query, string $severity)
    {
        return $query->whereJsonContains('properties->severity', $severity);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->whereJsonContains('properties->category', $category);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Override the properties cast to ensure it's always an array
     */
    protected function getPropertiesAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }

        return $value ?? [];
    }

    /**
     * Override the properties mutator to ensure proper JSON encoding
     */
    protected function setPropertiesAttribute($value)
    {
        $this->attributes['properties'] = is_array($value) ? json_encode($value) : $value;
    }
}
