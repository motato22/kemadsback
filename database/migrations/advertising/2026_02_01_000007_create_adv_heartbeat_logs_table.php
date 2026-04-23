<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adv_heartbeat_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tablet_id')->constrained('adv_tablets')->cascadeOnDelete();
            $table->timestamp('reported_at')->comment('Timestamp del dispositivo');
            $table->timestamp('received_at')->useCurrent()->comment('Timestamp de recepción en servidor');
            $table->tinyInteger('battery_level')->unsigned()->nullable();
            $table->string('app_version', 16)->nullable();
            $table->decimal('lat', 9, 6)->nullable()->comment('GPS: requiere consentimiento LFPDPPP');
            $table->decimal('lng', 9, 6)->nullable()->comment('GPS: requiere consentimiento LFPDPPP');
            $table->json('raw_payload')->nullable()->comment('Payload completo para trazabilidad');

            $table->index(['tablet_id', 'received_at']);
            $table->index('received_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adv_heartbeat_logs');
    }
};
