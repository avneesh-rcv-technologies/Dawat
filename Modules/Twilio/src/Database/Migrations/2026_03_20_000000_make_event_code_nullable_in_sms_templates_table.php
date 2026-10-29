<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sms_templates', function (Blueprint $table) {
            $table->string('event_code')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('sms_templates', function (Blueprint $table) {
            $table->string('event_code')->nullable(false)->change();
        });
    }
};
