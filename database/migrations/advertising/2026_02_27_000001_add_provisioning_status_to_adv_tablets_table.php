<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adv_tablets', function (Blueprint $table) {
            $table->boolean('guardian_active')->default(false)->after('status');
            $table->boolean('player_installed')->default(false)->after('guardian_active');
            $table->string('player_version', 16)->nullable()->after('player_installed');
        });
    }

    public function down(): void
    {
        Schema::table('adv_tablets', function (Blueprint $table) {
            $table->dropColumn(['guardian_active', 'player_installed', 'player_version']);
        });
    }
};
