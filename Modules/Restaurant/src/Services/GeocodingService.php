<?php

namespace Modules\Restaurant\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    protected ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = fn_get_setting('general.google_maps_api_key');
    }

    /**
     * Get latitude and longitude from address components
     *
     * @param array $addressData
     * @return array|null ['latitude' => float, 'longitude' => float]
     */
    public function geocodeAddress(array $addressData): ?array
    {
        if (empty($this->apiKey)) {
            Log::warning('Google Maps API key not configured');
            return null;
        }

        // Build address string from components
        $addressString = $this->buildAddressString($addressData);

        if (empty($addressString)) {
            return null;
        }

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $addressString,
                'key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] === 'OK' && !empty($data['results'])) {
                    $location = $data['results'][0]['geometry']['location'];

                    return [
                        'latitude' => $location['lat'],
                        'longitude' => $location['lng'],
                    ];
                }

                Log::warning('Geocoding failed', [
                    'status' => $data['status'],
                    'address' => $addressString,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Geocoding API error', [
                'message' => $e->getMessage(),
                'address' => $addressString,
            ]);
        }

        return null;
    }

    /**
     * Build address string from components
     *
     * @param array $addressData
     * @return string
     */
    protected function buildAddressString(array $addressData): string
    {
        $parts = [];

        if (!empty($addressData['address_line1'])) {
            $parts[] = $addressData['address_line1'];
        }

        if (!empty($addressData['address_line2'])) {
            $parts[] = $addressData['address_line2'];
        }

        if (!empty($addressData['city_name'])) {
            $parts[] = $addressData['city_name'];
        }

        if (!empty($addressData['state_name'])) {
            $parts[] = $addressData['state_name'];
        }

        if (!empty($addressData['postal_code'])) {
            $parts[] = $addressData['postal_code'];
        }

        if (!empty($addressData['country_name'])) {
            $parts[] = $addressData['country_name'];
        }

        return implode(', ', $parts);
    }

    /**
     * Check if API key is configured
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }
}
