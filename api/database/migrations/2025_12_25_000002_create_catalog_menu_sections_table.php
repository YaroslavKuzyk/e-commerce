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
        Schema::create('catalog_menu_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_menu_id')->constrained('catalog_menus')->cascadeOnDelete();
            $table->unsignedTinyInteger('column_index')->default(1);
            $table->string('name');
            $table->string('link', 500)->nullable();
            $table->foreignId('icon_file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('catalog_menu_id');
            $table->index(['catalog_menu_id', 'column_index', 'sort_order'], 'cms_menu_column_sort_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_menu_sections');
    }
};
