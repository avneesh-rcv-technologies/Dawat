<?php

use Modules\Countries\Repositories\CountryRepository;
use Modules\Countries\Repositories\CountryStateRepository;

if (!function_exists('fn_get_countries')) {
    /**
     * Get countries with optional repository filters.
     * Supported filters: id, ids, code, name, q, order_by, order_dir, paginate, per_page, limit
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\Paginator|\Illuminate\Database\Eloquent\Collection
     */
    function fn_get_countries(array $filters = []): mixed
    {
        $repo = new CountryRepository();
        return $repo->all($filters);
    }
}

if (!function_exists('fn_get_country')) {
    /**
     * Return single country by id or null.
     */
    function fn_get_country(int $id)
    {
        try {
            $repo = new CountryRepository();
            return $repo->find($id);
        } catch (\Throwable $e) {
            return null;
        }
    }
}

if (!function_exists('fn_get_country_by_code')) {
    function fn_get_country_by_code(string $code)
    {
        $list = fn_get_countries(['code' => $code]);
        if ($list instanceof \Illuminate\Contracts\Pagination\Paginator || $list instanceof \Illuminate\Database\Eloquent\Collection) {
            return $list->first() ?: null;
        }
        return null;
    }
}

if (!function_exists('fn_get_country_name')) {
    function fn_get_country_name(int $id = 0): ?string
    {
        $country = fn_get_country($id);
        return $country ? ($country->name ?? null) : null;
    }
}

// States helpers
if (!function_exists('fn_get_states')) {
    /**
     * Get states with optional filters.
     * Supported filters: id, ids, country_id, country_code, code, q, order_by, order_dir, paginate, per_page, limit
     */
    function fn_get_states(array $filters = []): mixed
    {
        $repo = new CountryStateRepository();
        return $repo->all($filters);
    }
}

if (!function_exists('fn_get_state')) {
    function fn_get_state(int $id)
    {
        try {
            $repo = new CountryStateRepository();
            return $repo->find($id);
        } catch (\Throwable $e) {
            return null;
        }
    }
}

if (!function_exists('fn_get_state_by_code')) {
    function fn_get_state_by_code(string $code)
    {
        $list = fn_get_states(['code' => $code]);
        if ($list instanceof \Illuminate\Contracts\Pagination\Paginator || $list instanceof \Illuminate\Database\Eloquent\Collection) {
            return $list->first() ?: null;
        }
        return null;
    }
}

if (!function_exists('fn_get_state_name')) {
    function fn_get_state_name(int $id = 0): ?string
    {
        $state = fn_get_state($id);
        return $state ? ($state->default_name ?? null) : null;
    }
}

if (!function_exists('fn_get_states_by_country')) {
    function fn_get_states_by_country(int $countryId)
    {
        return fn_get_states(['country_id' => $countryId]);
    }
}

