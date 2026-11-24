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
      \Modules\Core\Database\Seeders\Files::class,
      \Modules\Core\Database\Seeders\Settings::class,
      \Modules\Countries\Database\Seeders\CountriesTableSeeder::class,
      \Modules\Currencies\Database\Seeders\CurrencySeeder::class,
      \Modules\Acl\Database\Seeders\AdminSeeder::class,
      FoodManagementSeeder::class,
      ComprehensiveFoodSeeder::class,
      \Modules\Restaurant\Database\Seeders\RestaurantCancellationPolicySeeder::class,
      \Modules\Tax\Database\Seeders\TaxSeeder::class,
      \Modules\Orders\Database\Seeders\StatusesTableSeeder::class,


      \Modules\Discounts\Database\Seeders\DiscountsSeeder::class, // Not needed: demo discounts/coupons, duplicated on every run.

      \Modules\EmailNotification\Database\Seeders\EmailNotificationDatabaseSeeder::class,
      // \Modules\Notifications\Database\Seeders\NotificationSeeder::class, // Not needed: demo admin notifications, duplicated on every run.
      \Modules\Acl\Database\Seeders\CustomerNotificationSeeder::class,
      \Modules\KYC\Database\Seeders\OwnerIdentityDocumentSeeder::class,
      \Modules\KYC\Database\Seeders\KycTypeSeeder::class,
      \Modules\KYC\Database\Seeders\KycNotificationSeeder::class,
      \Modules\Orders\Database\Seeders\OrderNotificationSeeder::class,
      \Modules\Payments\Database\Seeders\PaymentNotificationSeeder::class,
      \Modules\Payments\Database\Seeders\CashPaymentNotificationSeeder::class,
      \Modules\Twilio\Database\Seeders\OtpNotificationSeeder::class,

      \Database\Seeders\DietaryLabelSeeder::class,
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
  }
}
