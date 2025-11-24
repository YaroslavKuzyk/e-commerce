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
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'menu_image_path']);
            $table->foreignId('logo_file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->foreignId('menu_image_file_id')->nullable()->constrained('files')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropForeign(['logo_file_id']);
            $table->dropForeign(['menu_image_file_id']);
            $table->dropColumn(['logo_file_id', 'menu_image_file_id']);
            $table->string('logo_path')->nullable();
            $table->string('menu_image_path')->nullable();
        });
    }
};
