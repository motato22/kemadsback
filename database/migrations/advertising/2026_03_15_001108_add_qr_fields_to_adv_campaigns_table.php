<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adv_campaigns', function (Blueprint $table) {
            $table->boolean('has_qr')->default(false)->after('ends_at');
            $table->string('qr_url')->nullable()->after('has_qr');
            $table->unsignedInteger('qr_scans')->default(0)->after('qr_url');
        });
    }

    public function down(): void
    {
        Schema::table('adv_campaigns', function (Blueprint $table) {
            $table->dropColumn(['has_qr', 'qr_url', 'qr_scans']);
        });
    }
};
