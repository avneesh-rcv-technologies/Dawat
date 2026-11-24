<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * One-time cleanup: removes every restaurant, every Restaurant Owner account,
 * and all data that belongs to them (menus, orders, payments, KYC, logs, tokens...).
 *
 * Runs once (tracked in the migrations table). Irreversible: take a DB backup first.
 */
return new class extends Migration
{
    private const RESTAURANT_TYPE = 'Modules\\Restaurant\\Models\\Restaurant';
    private const ADMIN_TYPE = 'Modules\\Acl\\Models\\Admin';

    public function up(): void
    {
        if (!Schema::hasTable('restaurants') || !Schema::hasTable('admins')) {
            return;
        }

        $restaurantIds = DB::table('restaurants')->pluck('id')->all();

        $ownerRoleIds = $this->ownerRoleIds();
        $ownerIds = DB::table('admins')
            ->whereIn('role_id', $ownerRoleIds ?: [0])
            ->pluck('id')
            ->merge(DB::table('restaurants')->whereNotNull('admin_id')->pluck('admin_id'))
            ->unique()
            ->values();

        // Never delete an account holding a protected/admin role, even if it is linked to a restaurant.
        $ownerIds = DB::table('admins')
            ->whereIn('id', $ownerIds->all() ?: [0])
            ->where(fn ($q) => $q->whereNull('role_id')->orWhereIn('role_id', $ownerRoleIds ?: [0]))
            ->pluck('id')
            ->all();

        $ownerEmails = DB::table('admins')->whereIn('id', $ownerIds ?: [0])->pluck('email')->all();

        Log::warning('Purging restaurant data', [
            'restaurants' => count($restaurantIds),
            'owner_accounts' => count($ownerIds),
        ]);

        Schema::disableForeignKeyConstraints();

        try {
            DB::transaction(function () use ($restaurantIds, $ownerIds, $ownerEmails) {
                $this->purgeOrders($restaurantIds, $ownerIds);
                $this->purgeMenus($restaurantIds);
                $this->purgeRestaurants($restaurantIds);
                $this->purgeOwners($ownerIds, $ownerEmails);
            });
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    public function down(): void
    {
        // Deleted data cannot be restored. Restore from a database backup if needed.
    }

    /** Restaurant Owner role id(s): the configured default role, else the role named "Restaurant Owner". */
    private function ownerRoleIds(): array
    {
        if (!Schema::hasTable('roles')) {
            return [];
        }

        $ids = DB::table('roles')
            ->whereRaw("LOWER(REPLACE(REPLACE(TRIM(name), '_', ' '), '-', ' ')) = 'restaurant owner'")
            ->pluck('id');

        if (Schema::hasTable('settings')) {
            $setting = DB::table('settings')->where('key', 'general.default_role')->value('value');
            $settingId = (int) json_decode((string) $setting, true);
            if ($settingId > 0) {
                $ids->push($settingId);
            }
        }

        // Never treat protected / platform admin roles as owner roles.
        $query = DB::table('roles')->whereIn('id', $ids->unique()->all() ?: [0]);
        if (Schema::hasColumn('roles', 'guard')) {
            $query->where('guard', 'admin');
        }
        $query->whereRaw("LOWER(TRIM(name)) NOT IN ('admin', 'admin owner', 'super admin')");

        return $query->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function purgeOrders(array $restaurantIds, array $ownerIds): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        $orderIds = DB::table('orders')
            ->where(function ($q) use ($restaurantIds, $ownerIds) {
                $q->where(fn ($q) => $q->where('vendor_type', self::RESTAURANT_TYPE)->whereIn('vendor_id', $restaurantIds ?: [0]))
                    ->orWhereIn('admin_id', $ownerIds ?: [0]);
            })
            ->pluck('id')
            ->all();

        foreach (array_chunk($orderIds, 500) as $chunk) {
            $this->deleteWhereIn('order_items', 'order_id', $chunk);
            $this->deleteWhereIn('transactions', 'order_id', $chunk);
            $this->deleteWhereIn('payments', 'order_id', $chunk);
            $this->deleteWhereIn('shippings', 'order_id', $chunk);
            $this->deleteWhereIn('coupon_usages', 'order_id', $chunk);
            if (Schema::hasTable('support_messages')) {
                DB::table('support_messages')->whereIn('order_id', $chunk)->update(['order_id' => null]);
            }
            DB::table('orders')->whereIn('id', $chunk)->delete();
        }

        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'vendor_type')) {
            $paymentIds = DB::table('payments')
                ->where('vendor_type', self::RESTAURANT_TYPE)
                ->whereIn('vendor_id', $restaurantIds ?: [0])
                ->pluck('id')->all();
            $this->deleteWhereIn('transactions', 'payment_id', $paymentIds);
            $this->deleteWhereIn('payments', 'id', $paymentIds);
        }
    }

    private function purgeMenus(array $restaurantIds): void
    {
        if (!$restaurantIds) {
            return;
        }

        $foodItemIds = Schema::hasTable('food_items')
            ? DB::table('food_items')->whereIn('restaurant_id', $restaurantIds)->pluck('id')->all()
            : [];
        $categoryIds = Schema::hasTable('food_categories')
            ? DB::table('food_categories')->whereIn('restaurant_id', $restaurantIds)->pluck('id')->all()
            : [];
        $featureIds = Schema::hasTable('food_features')
            ? DB::table('food_features')->whereIn('restaurant_id', $restaurantIds)->pluck('feature_id')->all()
            : [];

        foreach (['food_category_items', 'food_event_items', 'food_feature_values', 'food_item_option_groups',
                  'food_item_options', 'food_item_serving_options', 'food_item_tags'] as $table) {
            $this->deleteWhereIn($table, 'food_item_id', $foodItemIds);
        }
        $this->deleteFileLinks(['food_image'], $foodItemIds);
        $this->deleteWhereIn('food_items', 'id', $foodItemIds);

        $this->deleteWhereIn('food_category_items', 'food_category_id', $categoryIds);
        $this->deleteWhereIn('food_event_categories', 'food_category_id', $categoryIds);
        $this->deleteWhereIn('food_feature_categories', 'category_id', $categoryIds);
        $this->deleteFileLinks(['category_image'], $categoryIds);
        if (Schema::hasTable('food_categories')) {
            DB::table('food_categories')->whereIn('parent_id', $categoryIds ?: [0])->update(['parent_id' => null]);
        }
        $this->deleteWhereIn('food_categories', 'id', $categoryIds);

        if ($featureIds && Schema::hasTable('food_feature_variants')) {
            $variantIds = DB::table('food_feature_variants')->whereIn('feature_id', $featureIds)->pluck('variant_id')->all();
            $this->deleteWhereIn('food_feature_variant_descriptions', 'variant_id', $variantIds);
            $this->deleteWhereIn('food_feature_variants', 'variant_id', $variantIds);
        }
        foreach (['food_feature_categories', 'food_feature_descriptions', 'food_feature_values'] as $table) {
            $this->deleteWhereIn($table, 'feature_id', $featureIds);
        }
        $this->deleteWhereIn('food_features', 'feature_id', $featureIds);
    }

    private function purgeRestaurants(array $restaurantIds): void
    {
        foreach (['restaurant_banking_details', 'restaurant_certifications', 'restaurant_holidays', 'restaurant_hours',
                  'restaurant_onboarding_status_histories', 'restaurant_onboarding_steps'] as $table) {
            $this->deleteWhereIn($table, 'restaurant_id', $restaurantIds);
        }

        $this->deleteMorph('subscriptions', 'subscribable', self::RESTAURANT_TYPE, $restaurantIds, function ($ids) {
            $this->deleteWhereIn('subscription_features', 'subscription_id', $ids);
            $this->deleteWhereIn('subscription_reminders', 'subscription_id', $ids);
        });
        $this->deleteMorph('activity_log', 'subject', self::RESTAURANT_TYPE, $restaurantIds);

        $this->deleteWhereIn('restaurants', 'id', $restaurantIds);
    }

    private function purgeOwners(array $ownerIds, array $ownerEmails): void
    {
        if (!$ownerIds) {
            return;
        }

        $this->deleteWhereIn('restaurant_email_otps', 'admin_id', $ownerIds);

        if (Schema::hasTable('twilio_phone_numbers')) {
            $phoneIds = DB::table('twilio_phone_numbers')->whereIn('admin_id', $ownerIds)->pluck('id')->all();
            $this->deleteWhereIn('twilio_messages', 'phone_number_id', $phoneIds);
            $this->deleteWhereIn('twilio_phone_numbers', 'id', $phoneIds);
        }
        $this->deleteWhereIn('twilio_messages', 'admin_id', $ownerIds);
        $this->deleteWhereIn('twilio_messaging_automation', 'admin_id', $ownerIds);

        if (Schema::hasTable('kyc_submissions')) {
            $submissionIds = DB::table('kyc_submissions')->whereIn('user_id', $ownerIds)->pluck('id')->all();
            foreach (['kyc_files', 'kyc_messages', 'kyc_submission_data'] as $table) {
                $this->deleteWhereIn($table, 'submission_id', $submissionIds);
            }
            $this->deleteWhereIn('kyc_submissions', 'id', $submissionIds);
        }
        $this->deleteWhereIn('kyc_requests', 'requested_by', $ownerIds);

        $this->deleteMorph('subscriptions', 'subscribable', self::ADMIN_TYPE, $ownerIds, function ($ids) {
            $this->deleteWhereIn('subscription_features', 'subscription_id', $ids);
            $this->deleteWhereIn('subscription_reminders', 'subscription_id', $ids);
        });
        $this->deleteMorph('notifications', 'notifiable', self::ADMIN_TYPE, $ownerIds);
        $this->deleteMorph('notifications_log', 'notifiable', self::ADMIN_TYPE, $ownerIds);
        $this->deleteMorph('personal_access_tokens', 'tokenable', self::ADMIN_TYPE, $ownerIds);
        $this->deleteMorph('activity_log', 'causer', self::ADMIN_TYPE, $ownerIds);
        $this->deleteMorph('activity_log', 'subject', self::ADMIN_TYPE, $ownerIds);
        $this->deleteMorph('request_activity_logs', 'causer', self::ADMIN_TYPE, $ownerIds);
        $this->deleteMorph('request_activity_logs_archive', 'causer', self::ADMIN_TYPE, $ownerIds);
        $this->deleteMorph('admin_access_requests', 'requested_by', self::ADMIN_TYPE, $ownerIds);
        $this->deleteFileLinks(['admin_avatar'], $ownerIds);

        foreach (['admin_password_reset_tokens', 'password_reset_tokens'] as $table) {
            $this->deleteWhereIn($table, 'email', $ownerEmails);
        }

        $this->deleteWhereIn('admins', 'id', $ownerIds);
    }

    private function deleteWhereIn(string $table, string $column, array $values): void
    {
        if (!$values || !Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        foreach (array_chunk($values, 500) as $chunk) {
            DB::table($table)->whereIn($column, $chunk)->delete();
        }
    }

    private function deleteMorph(string $table, string $morph, string $type, array $ids, ?callable $beforeDelete = null): void
    {
        if (!$ids || !Schema::hasTable($table) || !Schema::hasColumn($table, "{$morph}_type")) {
            return;
        }

        $rowIds = DB::table($table)->where("{$morph}_type", $type)->whereIn("{$morph}_id", $ids)->pluck('id')->all();
        if ($beforeDelete) {
            $beforeDelete($rowIds);
        }
        $this->deleteWhereIn($table, 'id', $rowIds);
    }

    private function deleteFileLinks(array $objectTypes, array $objectIds): void
    {
        if (!$objectIds || !Schema::hasTable('file_links')) {
            return;
        }

        DB::table('file_links')->whereIn('object_type', $objectTypes)->whereIn('object_id', $objectIds)->delete();
    }
};
