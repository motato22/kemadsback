<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adv_campaign_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->comment('Ej: Ruta Norte, Flota Completa');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Pivot: tablets pertenecientes a un grupo
        Schema::create('adv_campaign_group_tablet', function (Blueprint $table) {
            $table->foreignId('campaign_group_id')->constrained('adv_campaign_groups')->cascadeOnDelete();
            $table->foreignId('tablet_id')->constrained('adv_tablets')->cascadeOnDelete();
            $table->primary(['campaign_group_id', 'tablet_id']);
        });

        // Pivot: campañas asignadas a un grupo
        Schema::create('adv_campaign_group_campaign', function (Blueprint $table) {
            $table->foreignId('campaign_group_id')->constrained('adv_campaign_groups')->cascadeOnDelete();
            $table->foreignId('campaign_id')->constrained('adv_campaigns')->cascadeOnDelete();
            $table->primary(['campaign_group_id', 'campaign_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adv_campaign_group_campaign');
        Schema::dropIfExists('adv_campaign_group_tablet');
        Schema::dropIfExists('adv_campaign_groups');
    }
};
