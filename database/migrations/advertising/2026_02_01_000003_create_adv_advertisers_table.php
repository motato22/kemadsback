<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adv_advertisers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->comment('Razón social del anunciante');
            $table->string('rfc', 13)->nullable()->comment('RFC para facturación');
            $table->string('contact_name', 80)->nullable();
            $table->string('contact_email', 120)->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adv_advertisers');
    }
};
