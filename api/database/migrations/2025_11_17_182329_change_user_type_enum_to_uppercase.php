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
        // Крок 1: Оновлюємо всі існуючі значення
        DB::table('users')
            ->where('type', 'Admin')
            ->update(['type' => 'ADMIN']);

        DB::table('users')
            ->where('type', 'Customer')
            ->update(['type' => 'CUSTOMER']);

        // Крок 2: Змінюємо enum колонку
        DB::statement("ALTER TABLE users MODIFY COLUMN type ENUM('ADMIN', 'CUSTOMER') NOT NULL DEFAULT 'CUSTOMER'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Повертаємо старі значення
        DB::table('users')
            ->where('type', 'ADMIN')
            ->update(['type' => 'Admin']);

        DB::table('users')
            ->where('type', 'CUSTOMER')
            ->update(['type' => 'Customer']);

        // Повертаємо старий enum
        DB::statement("ALTER TABLE users MODIFY COLUMN type ENUM('Admin', 'Customer') NOT NULL DEFAULT 'Customer'");
    }
};
