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
        Schema::table('product_variants', function (Blueprint $table) {
            // Override flag - if true, use variant's own discount/clearance settings
            $table->boolean('override_pricing')->default(false)->after('is_default');

            // Discount fields
            $table->decimal('discount_price', 10, 2)->nullable()->after('override_pricing');
            $table->decimal('discount_percent', 5, 2)->nullable()->after('discount_price');
            $table->timestamp('discount_starts_at')->nullable()->after('discount_percent');
            $table->timestamp('discount_ends_at')->nullable()->after('discount_starts_at');

            // Clearance fields
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
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn([
                'override_pricing',
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
