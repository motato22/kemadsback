<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adv_tablets', function (Blueprint $table) {
            $table->string('device_id', 64)->nullable()->comment('Android ID asignado durante el aprovisionamiento')->change();
        });
    }

    public function down(): void
    {
        Schema::table('adv_tablets', function (Blueprint $table) {
            $table->string('device_id', 64)->nullable(false)->change();
        });
    }
};
