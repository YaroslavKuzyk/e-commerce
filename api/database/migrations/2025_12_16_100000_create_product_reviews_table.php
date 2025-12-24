<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('author_name');
            $table->string('author_email');
            $table->tinyInteger('rating')->unsigned(); // 1-5
            $table->text('advantages')->nullable(); // Переваги
            $table->text('disadvantages')->nullable(); // Недоліки
            $table->text('comment')->nullable(); // Коментар
            $table->json('youtube_urls')->nullable(); // До 5 YouTube відео
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('notify_on_reply')->default(false);
            $table->timestamps();

            $table->index(['product_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
