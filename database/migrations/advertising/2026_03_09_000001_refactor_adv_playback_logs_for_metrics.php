<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Soltar la FK si existe (compatible con todas las versiones de Laravel)
        $fkExists = DB::select("
            SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE()
              AND TABLE_NAME = 'adv_playback_logs'
              AND CONSTRAINT_NAME = 'adv_playback_logs_tablet_id_foreign'
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ");
        if ($fkExists) {
            Schema::table('adv_playback_logs', function (Blueprint $table) {
                $table->dropForeign('adv_playback_logs_tablet_id_foreign');
            });
        }

        // 2. Soltar índices si existen
        $existingIndexes = array_column(DB::select("
            SELECT INDEX_NAME FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'adv_playback_logs'
        "), 'INDEX_NAME');

        Schema::table('adv_playback_logs', function (Blueprint $table) use ($existingIndexes) {
            foreach ([
                'adv_playback_logs_tablet_id_played_at_index',
                'adv_playback_logs_media_id_played_at_index',
                'adv_playback_logs_driver_shift_id_index',
            ] as $index) {
                if (in_array($index, $existingIndexes)) {
                    $table->dropIndex($index);
                }
            }
        });

        // 3. Eliminar columnas del esquema anterior (solo las que aún existen)
        $columnsToDrop = array_filter(
            ['media_id', 'driver_shift_id', 'played_at', 'completed'],
            fn ($col) => Schema::hasColumn('adv_playback_logs', $col)
        );
        if (! empty($columnsToDrop)) {
            Schema::table('adv_playback_logs', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn(array_values($columnsToDrop));
            });
        }

        // 4. Agregar nuevas columnas y FK del esquema de métricas (solo si aún no existen)
        $existingIndexesAfter = array_column(DB::select("
            SELECT INDEX_NAME FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'adv_playback_logs'
        "), 'INDEX_NAME');

        Schema::table('adv_playback_logs', function (Blueprint $table) use ($existingIndexesAfter) {
            if (! Schema::hasColumn('adv_playback_logs', 'campaign_id')) {
                $table->foreignId('campaign_id')->after('tablet_id')->constrained('adv_campaigns')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('adv_playback_logs', 'started_at')) {
                $table->timestamp('started_at')->after('campaign_id');
            }
            if (! Schema::hasColumn('adv_playback_logs', 'ended_at')) {
                $table->timestamp('ended_at')->nullable()->after('started_at');
            }
            if (! Schema::hasColumn('adv_playback_logs', 'duration_seconds')) {
                $table->unsignedInteger('duration_seconds')->default(0)->after('ended_at');
            }
            if (! in_array('adv_playback_logs_campaign_id_started_at_index', $existingIndexesAfter)) {
                $table->index(['campaign_id', 'started_at']);
            }
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
