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
            if (Schema::hasColumn('product_categories', 'logo_path')) {
                $table->dropColumn('logo_path');
            }
            if (Schema::hasColumn('product_categories', 'menu_image_path')) {
                $table->dropColumn('menu_image_path');
            }
            if (!Schema::hasColumn('product_categories', 'logo_file_id')) {
                $table->foreignId('logo_file_id')->nullable()->constrained('files')->nullOnDelete();
            }
            if (!Schema::hasColumn('product_categories', 'menu_image_file_id')) {
                $table->foreignId('menu_image_file_id')->nullable()->constrained('files')->nullOnDelete();
            }
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
