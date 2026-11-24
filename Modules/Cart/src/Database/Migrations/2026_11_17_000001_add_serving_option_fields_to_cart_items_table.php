<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->unsignedBigInteger('serving_option_id')->nullable()->after('item_id');
            $table->string('serve_type')->nullable()->after('serving_option_id');
            $table->string('serving_name')->nullable()->after('serve_type');
            $table->integer('guest_count')->default(1)->after('serving_name');

            $table->decimal('subtotal', 10, 2)->default(0)->after('guest_count');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('subtotal');
            $table->decimal('final_price', 10, 2)->default(0)->after('tax_amount');
            $table->decimal('discount_price', 10, 2)->default(0)->after('final_price');

            $table->string('display_title')->nullable()->after('discount_price');
            $table->string('display_subtitle')->nullable()->after('display_title');
            $table->string('display_price_label')->nullable()->after('display_subtitle');
            $table->string('display_unit_label')->nullable()->after('display_price_label');
            $table->string('display_unit_price_text')->nullable()->after('display_unit_label');
            $table->decimal('display_per_person_price', 10, 2)->nullable()->after('display_unit_price_text');

            $table->json('selected_option_snapshot')->nullable()->after('display_per_person_price');
            $table->text('note')->nullable()->after('selected_option_snapshot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn([
                'serving_option_id',
                'serve_type',
                'serving_name',
                'guest_count',
                'subtotal',
                'tax_amount',
                'final_price',
                'discount_price',
                'display_title',
                'display_subtitle',
                'display_price_label',
                'display_unit_label',
                'display_unit_price_text',
                'display_per_person_price',
                'selected_option_snapshot',
                'note',
            ]);
        });
    }
};
