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
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('short_description');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->foreignId('preview_image_id')->nullable()->constrained('files')->nullOnDelete();
            $table->string('status')->default('draft');
            $table->timestamp('publication_date')->nullable();
            $table->foreignId('blog_category_id')->constrained('blog_categories')->onDelete('cascade');
            $table->timestamps();

            $table->index('status');
            $table->index('publication_date');
            $table->index('blog_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
