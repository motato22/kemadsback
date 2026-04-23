<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('adv_surveys')) {
            Schema::create('adv_surveys', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('adv_campaigns')->cascadeOnDelete();
                $table->string('name', 120);
                $table->enum('type', ['survey', 'trivia'])->default('survey');
                $table->unsignedInteger('timeout_seconds')->default(30);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
            return;
        }

        // Tabla ya existe (estructura anterior): añadir columnas del Módulo 4
        Schema::table('adv_surveys', function (Blueprint $table) {
            if (! Schema::hasColumn('adv_surveys', 'name')) {
                $table->string('name', 120)->nullable()->after('campaign_id');
            }
            if (! Schema::hasColumn('adv_surveys', 'type')) {
                $table->enum('type', ['survey', 'trivia'])->nullable()->after('name');
            }
            if (! Schema::hasColumn('adv_surveys', 'timeout_seconds')) {
                $table->unsignedInteger('timeout_seconds')->default(30)->after('type');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('adv_surveys') && Schema::hasColumn('adv_surveys', 'name')) {
            Schema::table('adv_surveys', fn (Blueprint $table) => $table->dropColumn(['name', 'type', 'timeout_seconds']));
        }
        if (Schema::hasTable('adv_surveys') && ! Schema::hasColumn('adv_surveys', 'title')) {
            Schema::dropIfExists('adv_surveys');
        }
    }
};
