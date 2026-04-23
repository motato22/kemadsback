<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adv_playback_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tablet_id')->constrained('adv_tablets')->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('adv_media')->cascadeOnDelete();
            $table->foreignId('driver_shift_id')->nullable()->constrained('adv_driver_shifts')->nullOnDelete();
            $table->timestamp('played_at')->comment('Momento en que finalizó la reproducción');
            $table->boolean('completed')->default(true)->comment('true=reproducción completa, false=interrumpida');

            $table->index(['tablet_id', 'played_at']);
            $table->index(['media_id', 'played_at']);
            $table->index('driver_shift_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adv_playback_logs');
    }
};
