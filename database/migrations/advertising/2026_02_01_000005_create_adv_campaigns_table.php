<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('adv_campaigns')) {
            Schema::create('adv_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertiser_id')->constrained('adv_advertisers')->restrictOnDelete();
            $table->string('name', 120);
            $table->enum('status', ['scheduled', 'active', 'paused', 'expired'])->default('scheduled');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->timestamps();

            $table->index(['status', 'starts_at', 'ends_at']);
            $table->index('advertiser_id');
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('adv_campaigns');
    }
};
