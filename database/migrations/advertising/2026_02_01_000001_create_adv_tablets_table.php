<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adv_tablets', function (Blueprint $table) {
            $table->id();
            $table->string('device_id', 64)->unique()->comment('Android ID o MAC address único del hardware');
            $table->string('unit_id', 32)->comment('Número de unidad de la camioneta asignada');
            $table->string('name', 80)->nullable()->comment('Nombre descriptivo para el panel');
            $table->enum('status', ['active', 'inactive', 'provisioning'])->default('provisioning');
            $table->timestamp('last_seen_at')->nullable()->comment('Último heartbeat recibido');
            $table->tinyInteger('battery_level')->unsigned()->nullable()->comment('0-100');
            $table->string('app_version', 16)->nullable()->comment('Versión de la Reproductora instalada');
            $table->unsignedBigInteger('sanctum_token_id')->nullable()->comment('FK al Personal Access Token activo');
            $table->timestamps();

            $table->index('status');
            $table->index('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adv_tablets');
    }
};
