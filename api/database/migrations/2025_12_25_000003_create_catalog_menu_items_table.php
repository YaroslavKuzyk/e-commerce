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
        Schema::create('catalog_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_menu_section_id')->constrained('catalog_menu_sections')->cascadeOnDelete();
            $table->string('name');
            $table->string('link', 500);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('catalog_menu_section_id');
            $table->index(['catalog_menu_section_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_menu_items');
    }
};
