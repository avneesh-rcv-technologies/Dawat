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
      // \Modules\Core\Database\Seeders\Roles::class, // Not needed: empty, run() body is fully commented out.
      // \Modules\Core\Database\Seeders\Files::class, // Not needed: inserts duplicate logo/favicon file rows on every run.
      // \Modules\Core\Database\Seeders\Settings::class,
      // \Modules\Countries\Database\Seeders\CountriesTableSeeder::class, // Not needed: deletes all countries, states and cities before re-inserting.
      // \Modules\Currencies\Database\Seeders\CurrencySeeder::class,

      // Accounts must exist before dependent records.
      // \Modules\Acl\Database\Seeders\AdminSeeder::class,
      // UserSeeder::class, // Not needed: dummy test users (password123).
      // CustomerSeeder::class, // Not needed: dummy test customers (password123).

      // Use one food dataset to avoid overlapping seed data.
      // FoodManagementSeeder::class,
      // ComprehensiveFoodSeeder::class,
      // \Modules\Restaurant\Database\Seeders\RestaurantCancellationPolicySeeder::class, // Not needed: overwrites cancellation policy of every restaurant.
      // \Modules\Tax\Database\Seeders\TaxSeeder::class,
      // \Modules\Orders\Database\Seeders\StatusesTableSeeder::class,

  
      // \Modules\Discounts\Database\Seeders\DiscountsSeeder::class, // Not needed: demo discounts/coupons, duplicated on every run.
      // Notification channels and KYC configuration.
      // \Modules\EmailNotification\Database\Seeders\EmailNotificationDatabaseSeeder::class,
      // // \Modules\Notifications\Database\Seeders\NotificationSeeder::class, // Not needed: demo admin notifications, duplicated on every run.
      // \Modules\Acl\Database\Seeders\CustomerNotificationSeeder::class,
      // // \Modules\KYC\Database\Seeders\OwnerIdentityDocumentSeeder::class,
      // // \Modules\KYC\Database\Seeders\KycTypeSeeder::class, // Not needed: deletes all KYC types (cascades to KYC submissions/requests).
      // \Modules\KYC\Database\Seeders\KycNotificationSeeder::class,
      // \Modules\Orders\Database\Seeders\OrderNotificationSeeder::class,
      // \Modules\Payments\Database\Seeders\PaymentNotificationSeeder::class,
      // \Modules\Payments\Database\Seeders\CashPaymentNotificationSeeder::class,
      // \Modules\Twilio\Database\Seeders\OtpNotificationSeeder::class,
      // // \Modules\Twilio\Database\Seeders\MessagingAutomationSeeder::class, // Not needed: demo auto-reply rules, duplicated on every run.

      // // Latest dietary labels, onboarding, and notification updates.
      // \Database\Seeders\DietaryLabelSeeder::class,
      // \Modules\DietaryManagment\Database\Seeders\DietaryNotificationSeeder::class,
      // \Modules\Acl\Database\Seeders\AdminEmailVerificationSeeder::class,
      // \Modules\Acl\Database\Seeders\AdminPasswordResetNotificationSeeder::class,
      // \Modules\Acl\Database\Seeders\CustomerPasswordResetNotificationSeeder::class,
      // \Modules\Restaurant\Database\Seeders\RestaurantNotificationSeeder::class,
      // \Modules\Restaurant\Database\Seeders\RestaurantOnboardingEmailVerificationSeeder::class,
      // \Modules\Restaurant\Database\Seeders\RestaurantPasswordResetNotificationSeeder::class,
      // \Modules\Restaurant\Database\Seeders\RestaurantBankingNotificationSeeder::class,
      // \Modules\Restaurant\Database\Seeders\RestaurantCommissionAcceptedNotificationSeeder::class,
      // \Modules\Restaurant\Database\Seeders\RestaurantTaxNotificationSeeder::class,
      // \Modules\Restaurant\Database\Seeders\FinalOnboardingApprovedNotificationSeeder::class,
      // \Modules\Restaurant\Database\Seeders\RestaurantResubmittedDetailsNotificationSeeder::class,
      // \Modules\Restaurant\Database\Seeders\OnboardingRefillRequestedNotificationSeeder::class,
      // \Modules\KYC\Database\Seeders\KycStatusNotificationSeeder::class,
      // \Modules\KYC\Database\Seeders\KycMessageNotificationSeeder::class,
      // \Modules\KYC\Database\Seeders\KycDocumentSubmissionNotificationSeeder::class,
      // \Modules\KYC\Database\Seeders\KycDocumentReSubmissionNotificationSeeder::class,
      // \Modules\Activitylog\Database\Seeders\OtpAccessRequestSeeder::class,

      // Must run last: links any event still missing an email template mapping.
      // NotificationTemplateMappingSeeder::class,
    ]);
  }
}
