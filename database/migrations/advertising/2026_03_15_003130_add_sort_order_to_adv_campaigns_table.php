<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adv_campaigns', function (Blueprint $table) {
            // Añadimos sort_order con default 0
            $table->unsignedInteger('sort_order')->default(0)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('adv_campaigns', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
