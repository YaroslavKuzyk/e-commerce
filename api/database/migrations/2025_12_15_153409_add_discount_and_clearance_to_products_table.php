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
        Schema::table('products', function (Blueprint $table) {
            // Discount fields
            $table->decimal('discount_price', 10, 2)->nullable()->after('base_price');
            $table->decimal('discount_percent', 5, 2)->nullable()->after('discount_price');
            $table->timestamp('discount_starts_at')->nullable()->after('discount_percent');
            $table->timestamp('discount_ends_at')->nullable()->after('discount_starts_at');

            // Clearance (уцінка) fields
            $table->boolean('is_clearance')->default(false)->after('discount_ends_at');
            $table->decimal('clearance_price', 10, 2)->nullable()->after('is_clearance');
            $table->string('clearance_reason')->nullable()->after('clearance_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'discount_price',
                'discount_percent',
                'discount_starts_at',
                'discount_ends_at',
                'is_clearance',
                'clearance_price',
                'clearance_reason',
            ]);
        });
    }
};
