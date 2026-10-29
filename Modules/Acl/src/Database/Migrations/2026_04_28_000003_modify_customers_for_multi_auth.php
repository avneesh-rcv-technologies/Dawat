<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            
            $table->string('email')->nullable()->change();
            
           
            $table->string('password')->nullable()->change();
            
            
            $table->boolean('phone_verified')->default(false)->after('phone');
            $table->timestamp('phone_verified_at')->nullable()->after('phone_verified');
            
          
            $table->json('auth_methods')->nullable()->after('phone_verified_at')
                ->comment('Array of enabled auth methods: email, phone, google, apple');
            
          
            $table->enum('primary_auth_method', ['email', 'phone', 'google', 'apple'])
                ->nullable()
                ->after('auth_methods');
            
            
            $table->string('avatar_url')->nullable()->after('primary_auth_method');
            $table->boolean('email_verified')->default(false)->after('email');
            
    
            $table->index(['phone', 'phone_verified']);
            $table->index(['email', 'status']);
            $table->index(['primary_auth_method', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['phone', 'phone_verified']);
            $table->dropIndex(['email', 'status']);
            $table->dropIndex(['primary_auth_method', 'status']);
            
            $table->dropColumn([
                'phone_verified',
                'phone_verified_at',
                'auth_methods',
                'primary_auth_method',
                'avatar_url',
                'email_verified'
            ]);
            
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });
    }
};
