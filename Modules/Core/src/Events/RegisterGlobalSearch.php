<?php

namespace Modules\Core\Events;

class RegisterGlobalSearch
{
    /**
     * The search query string
     *
     * @var string
     */
    public string $query;

    /**
     * The module filter (e.g. 'all' or specific module key)
     *
     * @var string
     */
    public string $module;


    public array $searchPreferences = [];

    /**
     * Guards to use for global search
     *
     * @var array
     */
    public array $guards = [];

    /**
     * Collected results added by listeners
     *
     * @var array
     */
    public array $results = [];

    public function __construct(string $query = '', string $module = 'all')
    {
        $this->query = $query;
        $this->module = $module;
        // Default to config guards if not set manually
        $this->guards = array_keys(config('acl::guards.guards')) ?? ['admin'];
    }

    /**
     * Add a single result item.
     * Expected item keys: id, title, url, type, module, module_name
     *
     * @param array $item
     * @return void
     */
    public function addResult(array $item): void
    {
        $this->results[] = $item;
    }

    /**
     * Get all collected results
     *
     * @return array
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * Register a search preference entry (key => display name)
     *
     * @param string $key
     * @param string $name
     * @return void
     */
    public function addSearchPreference(string $key, string $name): void
    {
        $this->searchPreferences[$key] = $name;
    }

    /**
     * Get all registered search preferences
     *
     * @return array
     */
    public function getSearchPreferences(): array
    {
        return $this->searchPreferences;
    }
}
