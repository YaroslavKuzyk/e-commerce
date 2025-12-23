<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify enum to include 'color' type
        DB::statement("ALTER TABLE attributes MODIFY COLUMN type ENUM('select', 'multi_select', 'checkbox', 'switch', 'color', 'text', 'number') DEFAULT 'select'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE attributes MODIFY COLUMN type ENUM('select', 'multi_select', 'checkbox', 'switch') DEFAULT 'select'");
    }
};
