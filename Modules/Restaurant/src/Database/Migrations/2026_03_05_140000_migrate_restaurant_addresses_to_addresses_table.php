<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing restaurant addresses from restaurants table to addresses table
        $restaurants = DB::table('restaurants')->get();

        foreach ($restaurants as $restaurant) {
            // Only create address if restaurant has address data
            if ($restaurant->address_line1 || $restaurant->city) {
                // Check if address already exists
                $existingAddress = DB::table('addresses')
                    ->where('addressable_type', 'Modules\\Restaurant\\Models\\Restaurant')
                    ->where('addressable_id', $restaurant->id)
                    ->where('address_type', 'primary')
                    ->first();

                if (!$existingAddress) {
                    // Find city_id, state_id, country_id based on names
                    $cityId = null;
                    $stateId = null;
                    $countryId = null;

                    if ($restaurant->country) {
                        $country = DB::table('countries')
                            ->where('name', 'LIKE', '%' . $restaurant->country . '%')
                            ->first();
                        if ($country) {
                            $countryId = $country->id;
                        }
                    }

                    if ($restaurant->state && $countryId) {
                        $state = DB::table('country_states')
                            ->where('country_id', $countryId)
                            ->where('name', 'LIKE', '%' . $restaurant->state . '%')
                            ->first();
                        if ($state) {
                            $stateId = $state->id;
                        }
                    }

                    if ($restaurant->city && $stateId) {
                        $city = DB::table('cities')
                            ->where('state_id', $stateId)
                            ->where('name', 'LIKE', '%' . $restaurant->city . '%')
                            ->first();
                        if ($city) {
                            $cityId = $city->id;
                        }
                    }

                    // Create address record
                    DB::table('addresses')->insert([
                        'addressable_type' => 'Modules\\Restaurant\\Models\\Restaurant',
                        'addressable_id' => $restaurant->id,
                        'address_type' => 'primary',
                        'address_line_1' => $restaurant->address_line1,
                        'address_line_2' => $restaurant->address_line2,
                        'city_id' => $cityId,
                        'state_id' => $stateId,
                        'country_id' => $countryId,
                        'zip_code' => $restaurant->postal_code,
                        'latitude' => $restaurant->latitude,
                        'longitude' => $restaurant->longitude,
                        'is_default' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove migrated restaurant addresses
        DB::table('addresses')
            ->where('addressable_type', 'Modules\\Restaurant\\Models\\Restaurant')
            ->where('address_type', 'primary')
            ->delete();
    }
};
