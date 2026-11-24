<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class NotificationSetupSeeder extends Seeder
{
    public function run(): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () {
            $this->call([
                NotificationChannelsSeeder::class,
                \Modules\EmailNotification\Database\Seeders\EmailNotificationDatabaseSeeder::class,
                \Modules\Acl\Database\Seeders\CustomerNotificationSeeder::class,
                \Modules\KYC\Database\Seeders\KycNotificationSeeder::class,
                \Modules\Orders\Database\Seeders\OrderNotificationSeeder::class,
                \Modules\Payments\Database\Seeders\PaymentNotificationSeeder::class,
                \Modules\Payments\Database\Seeders\CashPaymentNotificationSeeder::class,
                \Modules\Twilio\Database\Seeders\OtpNotificationSeeder::class,
                \Modules\DietaryManagment\Database\Seeders\DietaryNotificationSeeder::class,
                \Modules\Acl\Database\Seeders\AdminEmailVerificationSeeder::class,
                \Modules\Acl\Database\Seeders\AdminPasswordResetNotificationSeeder::class,
                \Modules\Acl\Database\Seeders\CustomerPasswordResetNotificationSeeder::class,
                \Modules\Restaurant\Database\Seeders\RestaurantNotificationSeeder::class,
                \Modules\Restaurant\Database\Seeders\RestaurantOnboardingEmailVerificationSeeder::class,
                \Modules\Restaurant\Database\Seeders\RestaurantPasswordResetNotificationSeeder::class,
                \Modules\Restaurant\Database\Seeders\RestaurantBankingNotificationSeeder::class,
                \Modules\Restaurant\Database\Seeders\RestaurantCommissionAcceptedNotificationSeeder::class,
                \Modules\Restaurant\Database\Seeders\RestaurantTaxNotificationSeeder::class,
                \Modules\Restaurant\Database\Seeders\FinalOnboardingApprovedNotificationSeeder::class,
                \Modules\Restaurant\Database\Seeders\RestaurantResubmittedDetailsNotificationSeeder::class,
                \Modules\Restaurant\Database\Seeders\OnboardingRefillRequestedNotificationSeeder::class,
                \Modules\KYC\Database\Seeders\KycStatusNotificationSeeder::class,
                \Modules\KYC\Database\Seeders\KycMessageNotificationSeeder::class,
                \Modules\KYC\Database\Seeders\KycDocumentSubmissionNotificationSeeder::class,
                \Modules\KYC\Database\Seeders\KycDocumentReSubmissionNotificationSeeder::class,
                \Modules\Activitylog\Database\Seeders\OtpAccessRequestSeeder::class,
                NotificationTemplateMappingSeeder::class,
            ]);
        });
    }
}
