<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Soltar la FK primero (MySQL no permite eliminar índices usados por FKs)
        Schema::table('adv_playback_logs', function (Blueprint $table) {
            $table->dropForeign('adv_playback_logs_tablet_id_foreign');
        });

        // 2. Ahora sí podemos eliminar los índices
        Schema::table('adv_playback_logs', function (Blueprint $table) {
            $table->dropIndex('adv_playback_logs_tablet_id_played_at_index');
            $table->dropIndex('adv_playback_logs_media_id_played_at_index');
            $table->dropIndex('adv_playback_logs_driver_shift_id_index');
        });

        // 3. Eliminar columnas del esquema anterior
        Schema::table('adv_playback_logs', function (Blueprint $table) {
            $table->dropColumn(['media_id', 'driver_shift_id', 'played_at', 'completed']);
        });

        // 4. Agregar nuevas columnas y FK del esquema de métricas
        Schema::table('adv_playback_logs', function (Blueprint $table) {
            $table->foreignId('campaign_id')->after('tablet_id')->constrained('adv_campaigns')->cascadeOnDelete();
            $table->timestamp('started_at')->after('campaign_id');
            $table->timestamp('ended_at')->nullable()->after('started_at');
            $table->unsignedInteger('duration_seconds')->default(0)->after('ended_at');
            $table->index(['campaign_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::table('adv_playback_logs', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->dropIndex(['campaign_id', 'started_at']);
            $table->dropColumn(['campaign_id', 'started_at', 'ended_at', 'duration_seconds']);
        });

        Schema::table('adv_playback_logs', function (Blueprint $table) {
            $table->foreignId('media_id')->after('tablet_id')->constrained('adv_media')->cascadeOnDelete();
            $table->foreignId('driver_shift_id')->nullable()->after('media_id')->constrained('adv_driver_shifts')->nullOnDelete();
            $table->timestamp('played_at')->after('driver_shift_id')->comment('Momento en que finalizó la reproducción');
            $table->boolean('completed')->default(true)->after('played_at')->comment('true=reproducción completa, false=interrumpida');
            $table->index(['tablet_id', 'played_at']);
            $table->index(['media_id', 'played_at']);
            $table->index('driver_shift_id');
        });
    }
};
