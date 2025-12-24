<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Changes product_id to product_variant_id in cart_items, user_favorites, and user_comparisons tables.
     */
    public function up(): void
    {
        // Clear data from tables since we're changing from products to product_variants
        DB::table('cart_items')->truncate();
        DB::table('user_favorites')->truncate();
        DB::table('user_comparisons')->truncate();

        // Update cart_items table
        // First add a temporary index on user_id so we can drop the unique constraint
        DB::statement('CREATE INDEX cart_items_user_id_temp_idx ON cart_items (user_id)');
        DB::statement('ALTER TABLE cart_items DROP INDEX cart_items_user_id_product_id_unique');
        DB::statement('ALTER TABLE cart_items DROP INDEX cart_items_product_id_index');
        DB::statement('ALTER TABLE cart_items CHANGE COLUMN product_id product_variant_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE cart_items ADD CONSTRAINT cart_items_product_variant_id_foreign FOREIGN KEY (product_variant_id) REFERENCES product_variants(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE cart_items ADD UNIQUE cart_items_user_id_product_variant_id_unique (user_id, product_variant_id)');
        DB::statement('ALTER TABLE cart_items ADD INDEX cart_items_product_variant_id_index (product_variant_id)');
        // Drop the temporary index
        DB::statement('DROP INDEX cart_items_user_id_temp_idx ON cart_items');

        // Update user_favorites table
        DB::statement('CREATE INDEX user_favorites_user_id_temp_idx ON user_favorites (user_id)');
        DB::statement('ALTER TABLE user_favorites DROP FOREIGN KEY user_favorites_product_id_foreign');
        DB::statement('ALTER TABLE user_favorites DROP INDEX user_favorites_user_id_product_id_unique');
        DB::statement('ALTER TABLE user_favorites DROP INDEX user_favorites_product_id_index');
        DB::statement('ALTER TABLE user_favorites CHANGE COLUMN product_id product_variant_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE user_favorites ADD CONSTRAINT user_favorites_product_variant_id_foreign FOREIGN KEY (product_variant_id) REFERENCES product_variants(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE user_favorites ADD UNIQUE user_favorites_user_id_product_variant_id_unique (user_id, product_variant_id)');
        DB::statement('ALTER TABLE user_favorites ADD INDEX user_favorites_product_variant_id_index (product_variant_id)');
        DB::statement('DROP INDEX user_favorites_user_id_temp_idx ON user_favorites');

        // Update user_comparisons table
        DB::statement('CREATE INDEX user_comparisons_user_id_temp_idx ON user_comparisons (user_id)');
        DB::statement('ALTER TABLE user_comparisons DROP FOREIGN KEY user_comparisons_product_id_foreign');
        DB::statement('ALTER TABLE user_comparisons DROP INDEX user_comparisons_user_id_product_id_unique');
        DB::statement('ALTER TABLE user_comparisons DROP INDEX user_comparisons_product_id_index');
        DB::statement('ALTER TABLE user_comparisons CHANGE COLUMN product_id product_variant_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE user_comparisons ADD CONSTRAINT user_comparisons_product_variant_id_foreign FOREIGN KEY (product_variant_id) REFERENCES product_variants(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE user_comparisons ADD UNIQUE user_comparisons_user_id_product_variant_id_unique (user_id, product_variant_id)');
        DB::statement('ALTER TABLE user_comparisons ADD INDEX user_comparisons_product_variant_id_index (product_variant_id)');
        DB::statement('DROP INDEX user_comparisons_user_id_temp_idx ON user_comparisons');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear data from tables
        DB::table('cart_items')->truncate();
        DB::table('user_favorites')->truncate();
        DB::table('user_comparisons')->truncate();

        // Revert cart_items table
        DB::statement('CREATE INDEX cart_items_user_id_temp_idx ON cart_items (user_id)');
        DB::statement('ALTER TABLE cart_items DROP FOREIGN KEY cart_items_product_variant_id_foreign');
        DB::statement('ALTER TABLE cart_items DROP INDEX cart_items_user_id_product_variant_id_unique');
        DB::statement('ALTER TABLE cart_items DROP INDEX cart_items_product_variant_id_index');
        DB::statement('ALTER TABLE cart_items CHANGE COLUMN product_variant_id product_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE cart_items ADD CONSTRAINT cart_items_product_id_foreign FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE cart_items ADD UNIQUE cart_items_user_id_product_id_unique (user_id, product_id)');
        DB::statement('ALTER TABLE cart_items ADD INDEX cart_items_product_id_index (product_id)');
        DB::statement('DROP INDEX cart_items_user_id_temp_idx ON cart_items');

        // Revert user_favorites table
        DB::statement('CREATE INDEX user_favorites_user_id_temp_idx ON user_favorites (user_id)');
        DB::statement('ALTER TABLE user_favorites DROP FOREIGN KEY user_favorites_product_variant_id_foreign');
        DB::statement('ALTER TABLE user_favorites DROP INDEX user_favorites_user_id_product_variant_id_unique');
        DB::statement('ALTER TABLE user_favorites DROP INDEX user_favorites_product_variant_id_index');
        DB::statement('ALTER TABLE user_favorites CHANGE COLUMN product_variant_id product_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE user_favorites ADD CONSTRAINT user_favorites_product_id_foreign FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE user_favorites ADD UNIQUE user_favorites_user_id_product_id_unique (user_id, product_id)');
        DB::statement('ALTER TABLE user_favorites ADD INDEX user_favorites_product_id_index (product_id)');
        DB::statement('DROP INDEX user_favorites_user_id_temp_idx ON user_favorites');

        // Revert user_comparisons table
        DB::statement('CREATE INDEX user_comparisons_user_id_temp_idx ON user_comparisons (user_id)');
        DB::statement('ALTER TABLE user_comparisons DROP FOREIGN KEY user_comparisons_product_variant_id_foreign');
        DB::statement('ALTER TABLE user_comparisons DROP INDEX user_comparisons_user_id_product_variant_id_unique');
        DB::statement('ALTER TABLE user_comparisons DROP INDEX user_comparisons_product_variant_id_index');
        DB::statement('ALTER TABLE user_comparisons CHANGE COLUMN product_variant_id product_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE user_comparisons ADD CONSTRAINT user_comparisons_product_id_foreign FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE user_comparisons ADD UNIQUE user_comparisons_user_id_product_id_unique (user_id, product_id)');
        DB::statement('ALTER TABLE user_comparisons ADD INDEX user_comparisons_product_id_index (product_id)');
        DB::statement('DROP INDEX user_comparisons_user_id_temp_idx ON user_comparisons');
    }
};
