<?php

namespace Modules\Countries\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CountriesTableSeeder extends Seeder
{
    public function run()
    {
        $countries = json_decode(file_get_contents(__DIR__ . '/../../Data/countries.json'), true) ?? [];
        $states = json_decode(file_get_contents(__DIR__ . '/../../Data/states.json'), true) ?? [];
        $cities = json_decode(file_get_contents(__DIR__ . '/../../Data/cities.json'), true) ?? [];

        $usCountries = array_values(array_filter($countries, function ($country) {
            $countryCode = strtoupper((string) ($country['code'] ?? ''));
            $countryName = strtoupper((string) ($country['name'] ?? ''));

            return $countryCode === 'US'
                || $countryName === 'UNITED STATES'
                || $countryName === 'UNITED STATES OF AMERICA';
        }));

        if (empty($usCountries)) {
            throw new \RuntimeException('United States country record not found in countries.json');
        }

        $usCountryIds = array_map(function ($country) {
            return (string) ($country['id'] ?? '');
        }, $usCountries);

        $usStates = array_values(array_filter($states, function ($state) use ($usCountryIds) {
            $countryId = (string) ($state['country_id'] ?? '');
            $countryCode = strtoupper((string) ($state['country_code'] ?? ''));

            return $countryCode === 'US' || in_array($countryId, $usCountryIds, true);
        }));

        $usCities = array_values(array_filter($cities, function ($city) {
            return strtoupper((string) ($city['country_code'] ?? '')) === 'US';
        }));

        DB::transaction(function () use ($usCountries, $usStates, $usCities) {
            DB::table('cities')->delete();
            DB::table('country_states')->delete();
            DB::table('countries')->delete();

            foreach ($usCountries as $country) {
                DB::table('countries')->updateOrInsert(
                    ['id' => $country['id']],
                    [
                        'name' => $country['name'],
                        'iso3' => $country['iso3'],
                        'numeric_code' => $country['numeric_code'],
                        'code' => $country['code'],
                        'phonecode' => $country['phonecode'],
                        'capital' => $country['capital'],
                        'currency' => $country['currency'],
                        'region' => $country['region'],
                        'subregion' => $country['subregion'],
                        'latitude' => $country['latitude'],
                        'longitude' => $country['longitude'],
                        'flag' => $country['emoji'],
                        'wikiDataId' => $country['wikiDataId'],
                    ]
                );
            }

            foreach ($usStates as $state) {
                DB::table('country_states')->updateOrInsert(
                    ['id' => $state['id']],
                    [
                        'name' => $state['name'],
                        'country_id' => $state['country_id'],
                        'country_code' => $state['country_code'] ?? null,
                        'code' => $state['code'],
                        'latitude' => $state['latitude'],
                        'longitude' => $state['longitude'],
                        'flag' => $state['flag'] ?? null,
                        'wikiDataId' => $state['wikiDataId'] ?? null,
                    ]
                );
            }

            foreach ($usCities as $city) {
                DB::table('cities')->updateOrInsert(
                    ['id' => $city['id']],
                    [
                        'name' => $city['name'],
                        'state_id' => $city['state_id'],
                        'country_id' => $city['country_id'],
                        'latitude' => $city['latitude'],
                        'longitude' => $city['longitude'],
                        'flag' => $city['flag'] ?? null,
                        'wikiDataId' => $city['wikiDataId'] ?? null,
                    ]
                );
            }
        });

        Log::info('United States country, state, and city data seeded successfully.');
    }
}