<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    $this->call([
      // Core setup and reference data.
      \Modules\Core\Database\Seeders\Roles::class,
      \Modules\Core\Database\Seeders\Files::class,
      \Modules\Core\Database\Seeders\Settings::class,
      \Modules\Countries\Database\Seeders\CountriesTableSeeder::class,
      \Modules\Currencies\Database\Seeders\CurrencySeeder::class,

      // Accounts must exist before dependent records.
      \Modules\Acl\Database\Seeders\AdminSeeder::class,
      UserSeeder::class,
      CustomerSeeder::class,

      // Use one food dataset to avoid overlapping seed data.
      // FoodManagementSeeder::class,
      // ComprehensiveFoodSeeder::class,
      \Modules\Restaurant\Database\Seeders\RestaurantCancellationPolicySeeder::class,
      \Modules\Tax\Database\Seeders\TaxSeeder::class,
      \Modules\Orders\Database\Seeders\StatusesTableSeeder::class,

  
      \Modules\Discounts\Database\Seeders\DiscountsSeeder::class,
      // Notifications and KYC configuration.
      \Modules\Notifications\Database\Seeders\NotificationSeeder::class,
      \Modules\Acl\Database\Seeders\CustomerNotificationSeeder::class,
      \Modules\Acl\Database\Seeders\CustomerPasswordResetNotificationSeeder::class,
      \Modules\Acl\Database\Seeders\AdminEmailVerificationSeeder::class,
      \Modules\Restaurant\Database\Seeders\RestaurantNotificationSeeder::class,
      \Modules\Restaurant\Database\Seeders\RestaurantPasswordResetNotificationSeeder::class,
      \Modules\Restaurant\Database\Seeders\RestaurantBankingNotificationSeeder::class,
      \Modules\Restaurant\Database\Seeders\RestaurantTaxNotificationSeeder::class,
      \Modules\Restaurant\Database\Seeders\OnboardingRefillRequestedNotificationSeeder::class,
      // \Modules\KYC\Database\Seeders\OwnerIdentityDocumentSeeder::class,
      \Modules\KYC\Database\Seeders\KycTypeSeeder::class,
      \Modules\KYC\Database\Seeders\KycNotificationSeeder::class,
      \Modules\KYC\Database\Seeders\KycStatusNotificationSeeder::class,
      \Modules\KYC\Database\Seeders\KycMessageNotificationSeeder::class,
      \Modules\KYC\Database\Seeders\KycDocumentSubmissionNotificationSeeder::class,
      \Modules\Orders\Database\Seeders\OrderNotificationSeeder::class,
      \Modules\Payments\Database\Seeders\PaymentNotificationSeeder::class,
      \Modules\Twilio\Database\Seeders\OtpNotificationSeeder::class,
      \Modules\Twilio\Database\Seeders\MessagingAutomationSeeder::class,

      // add latest seeder to upadte 
      \Modules\Restaurant\Database\Seeders\RestaurantNotificationSeeder::class,
         \Modules\Restaurant\Database\Seeders\RestaurantBankingNotificationSeeder::class,
         \Modules\Restaurant\Database\Seeders\RestaurantTaxNotificationSeeder::class,
        \Modules\Restaurant\Database\Seeders\FinalOnboardingApprovedNotificationSeeder::class,
        \Modules\Restaurant\Database\Seeders\RestaurantResubmittedDetailsNotificationSeeder::class,
        \Modules\Acl\Database\Seeders\AdminEmailVerificationSeeder::class,
        \Modules\Acl\Database\Seeders\CustomerPasswordResetNotificationSeeder::class,
        \Modules\Restaurant\Database\Seeders\RestaurantPasswordResetNotificationSeeder::class,
        \Modules\Restaurant\Database\Seeders\OnboardingRefillRequestedNotificationSeeder::class,
        

    ]);
  }
}
