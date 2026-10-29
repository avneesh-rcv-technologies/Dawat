<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('mobile_banners', function (Blueprint $table) {
            $table->renameColumn('link_value', 'type_value');
        });
    }

    public function down()
    {
        Schema::table('mobile_banners', function (Blueprint $table) {
            $table->renameColumn('type_value', 'link_value');
        });
    }
};