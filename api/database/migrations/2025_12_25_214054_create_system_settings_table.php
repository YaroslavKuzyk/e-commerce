<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique(); // nova_poshta, ukrposhta, meest, monopay, liqpay, etc.
            $table->string('name'); // Display name
            $table->string('name_uk')->nullable(); // Ukrainian name
            $table->text('description')->nullable();
            $table->text('description_uk')->nullable();
            $table->json('data')->nullable(); // API keys and config
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
