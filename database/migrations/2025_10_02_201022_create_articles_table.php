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
        Schema::create('articles', function (Blueprint $table) {
            // Identification
            $table->id();
            $table->string('slug')->unique()->index();
            $table->string('provider_id')->nullable();
            $table->string('provider');
            // Content
            $table->mediumText('title');
            $table->text('description')->nullable();
            $table->longText('content');
            $table->mediumText('image_url')->nullable();
            // Metadata
            $table->unsignedBigInteger('source_id')->index()->nullable();
            $table->string('author')->index()->nullable();
            $table->mediumText('link');
            $table->dateTime('published_at')->index();
            $table->string('category')->index()->nullable();
            $table->string('language', 20)->nullable();
            // Search helpers
            $table->mediumText('keywords')->nullable(); // Comma-separated keywords
            $table->fullText(['title', 'description', 'keywords', 'content']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
