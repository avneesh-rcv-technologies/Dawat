<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Acl\Models\Admin;
use Modules\KYC\Models\KycFile;
use Modules\KYC\Models\KycSubmission;
use Modules\KYC\Models\KycSubmissionData;
use Modules\KYC\Models\KycType;
use Modules\Restaurant\Enums\OnboardingStatus;
use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Models\RestaurantHour;

/**
 * Creates a restaurant owner whose onboarding is fully complete
 * (registration, banking, tax and KYC all approved), so it can sign in
 * straight to the restaurant dashboard.
 *
 *   php artisan db:seed --class="Modules\Restaurant\Database\Seeders\DemoOnboardedRestaurantSeeder"
 *
 * Safe to run again: it updates the same account instead of creating duplicates.
 */
class DemoOnboardedRestaurantSeeder extends Seeder
{
    public const EMAIL = 'restaurant@dawat.test';
    public const PASSWORD = 'Dawat@12345';

    public function run(): void
    {
        DB::transaction(function () {
            $reviewer = $this->reviewer();
            $location = $this->location();
            $approvedAt = now();

            $owner = Admin::updateOrCreate(
                ['email' => self::EMAIL],
                [
                    'name' => 'Demo Restaurant Owner',
                    'password' => self::PASSWORD, // hashed by the Admin model cast
                    'status' => 1,
                    'role_id' => $this->restaurantRoleId(),
                    'email_verified_at' => $approvedAt,
                ]
            );

            $restaurant = Restaurant::updateOrCreate(
                ['admin_id' => $owner->id],
                [
                    'name' => 'Dawat Demo Kitchen',
                    'slug' => Restaurant::where('admin_id', $owner->id)->value('slug') ?? 'dawat-demo-kitchen-' . Str::lower(Str::random(6)),
                    'phone' => '+14155550123',
                    'business_license' => 'BL-DEMO-2026-0001',
                    'description' => 'Authentic Indian dishes cooked fresh every day. Seeded demo restaurant with completed onboarding.',
                    'address_line1' => '500 Market Street',
                    'city' => $location['city_name'],
                    'state' => $location['state_name'],
                    'country' => $location['country_name'],
                    'postal_code' => '94105',
                    'latitude' => 37.7897,
                    'longitude' => -122.4000,
                    'food_type' => 'all',
                    'is_takeaway' => true,
                    'is_delivery' => true,
                    'is_accepting_orders' => true,
                    'status' => 'active',
                    'approval_status' => 'approved',
                    'approved_at' => $approvedAt,
                    'approved_by' => $reviewer?->id,
                    'onboarding_status' => OnboardingStatus::APPROVED->value,
                    'onboarding_step' => OnboardingStep::KYC->order(),
                    'submitted_at' => $approvedAt,
                    'tax_type' => 'business',
                    'rejection_reason' => null,
                    'platform_commission_accepted' => true,
                    'platform_commission_percentage' => \Modules\Restaurant\Support\PlatformCommission::percentage() ?? 0,
                    'platform_commission_accepted_at' => $approvedAt,
                ]
            );

            // Primary address (the app reads restaurant addresses from the addresses table).
            $restaurant->addresses()->updateOrCreate(
                ['address_type' => 'primary'],
                [
                    'address_line_1' => '500 Market Street',
                    'address_line_2' => null,
                    'country_id' => $location['country_id'],
                    'state_id' => $location['state_id'],
                    'city_id' => $location['city_id'],
                    'city_name' => $location['city_name'],
                    'zip_code' => '94105',
                    'latitude' => 37.7897,
                    'longitude' => -122.4000,
                    'is_default' => true,
                ]
            );

            // Opening hours: every day, 10:00–22:00.
            $day = ['open' => true, 'from' => '10:00', 'to' => '22:00'];
            RestaurantHour::updateOrCreate(
                ['restaurant_id' => $restaurant->id],
                [
                    'hours' => array_fill_keys(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], $day),
                    'is_open_whole_day' => false,
                ]
            );

            // Banking (test routing/account numbers).
            $restaurant->bankingDetail()->updateOrCreate(
                [],
                [
                    'account_holder_name' => 'Dawat Demo Kitchen LLC',
                    'routing_number' => '110000000',
                    'account_number' => '000123456789',
                    'business_legal_name' => 'Dawat Demo Kitchen LLC',
                    'doing_business_as' => 'Dawat Demo Kitchen',
                    'address' => '500 Market Street',
                    'country_id' => $location['country_id'],
                    'state_id' => $location['state_id'],
                    'city_id' => $location['city_id'],
                    'postal_code' => '94105',
                ]
            );

            // Tax information (business / EIN).
            $restaurant->taxInformation()->updateOrCreate(
                ['tax_type' => 'business'],
                [
                    'company_legal_name' => 'Dawat Demo Kitchen LLC',
                    'doing_business_as' => 'Dawat Demo Kitchen',
                    'ein' => '12-3456789',
                    'address' => '500 Market Street',
                    'country_id' => $location['country_id'],
                    'state_id' => $location['state_id'],
                    'city_id' => $location['city_id'],
                    'zip_code' => '94105',
                    'same_mailing_address' => true,
                    'receive_1099_electronically' => true,
                    'backup_tax_withholding' => false,
                    'tax_certification' => true,
                    'tax_certified_at' => $approvedAt,
                ]
            );

            // KYC: one approved submission (with a placeholder PDF) for every active KYC document type.
            $this->approveKycDocuments($owner, $reviewer, $approvedAt);

            // Every onboarding step submitted and approved.
            foreach (OnboardingStep::ordered() as $step) {
                $restaurant->onboardingSteps()->updateOrCreate(
                    ['step_key' => $step->value],
                    [
                        'status' => OnboardingStatus::APPROVED->value,
                        'submitted_at' => $approvedAt,
                        'reviewed_by' => $reviewer?->id,
                        'reviewed_at' => $approvedAt,
                        'rejection_reason' => null,
                    ]
                );
            }
        });

        $this->command?->info('Demo restaurant with completed onboarding is ready.');
        $this->command?->line('  Login:    ' . route('restaurant.login.form'));
        $this->command?->line('  Email:    ' . self::EMAIL);
        $this->command?->line('  Password: ' . self::PASSWORD);
    }

    protected function approveKycDocuments(Admin $owner, ?Admin $reviewer, $approvedAt): void
    {
        $types = KycType::active()->kyc()->with('fields')->get();

        foreach ($types as $type) {
            $submission = KycSubmission::updateOrCreate(
                ['user_id' => $owner->id, 'kyc_type_id' => $type->id],
                [
                    'status' => 'approved',
                    'reviewed_by' => $reviewer?->id,
                    'reviewed_at' => $approvedAt,
                    'rejection_reason' => null,
                ]
            );

            foreach ($type->fields as $field) {
                $value = 'Demo ' . $field->field_name;

                if ($field->isFileType()) {
                    $path = "kyc/{$owner->id}/{$submission->id}/{$field->field_key}.pdf";
                    Storage::disk('public')->put($path, $this->placeholderPdf($type->name));

                    KycFile::updateOrCreate(
                        ['submission_id' => $submission->id, 'field_id' => $field->id],
                        ['file_path' => $path, 'file_type' => 'application/pdf', 'uploaded_at' => $approvedAt]
                    );
                    $value = 'file_uploaded';
                }

                KycSubmissionData::updateOrCreate(
                    ['submission_id' => $submission->id, 'field_id' => $field->id],
                    ['field_key' => $field->field_key, 'field_value' => $value]
                );
            }
        }
    }

    /** The platform admin recorded as the reviewer of every step. */
    protected function reviewer(): ?Admin
    {
        return Admin::whereHas('role', fn ($q) => $q->where('permission_type', 'all'))->orderBy('id')->first();
    }

    protected function restaurantRoleId(): int
    {
        return (int) (DB::table('roles')->where('name', 'Restaurant Owner')->value('id')
            ?: fn_get_setting('general.default_role')
            ?: 2);
    }

    /** San Francisco, CA, USA (falls back to the first available country/state/city). */
    protected function location(): array
    {
        $country = DB::table('countries')->where('code', 'US')->first() ?? DB::table('countries')->orderBy('id')->first();
        $state = DB::table('country_states')->where('country_id', $country->id)->where('name', 'California')->first()
            ?? DB::table('country_states')->where('country_id', $country->id)->orderBy('id')->first();
        $city = DB::table('cities')->where('state_id', $state->id)->where('name', 'San Francisco')->first()
            ?? DB::table('cities')->where('state_id', $state->id)->orderBy('id')->first();

        return [
            'country_id' => $country->id,
            'country_name' => $country->name,
            'state_id' => $state->id,
            'state_name' => $state->name,
            'city_id' => $city->id,
            'city_name' => $city->name,
        ];
    }

    /** A tiny valid one-page PDF so the KYC document links open in the admin. */
    protected function placeholderPdf(string $title): string
    {
        $text = 'Dawat demo document: ' . preg_replace('/[^A-Za-z0-9 \-]/', '', $title);
        $stream = "BT /F1 18 Tf 72 720 Td ({$text}) Tj ET";
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length ' . strlen($stream) . " >>\nstream\n{$stream}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $i => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1) . " 0 obj\n{$object}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf . "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";
    }
}
