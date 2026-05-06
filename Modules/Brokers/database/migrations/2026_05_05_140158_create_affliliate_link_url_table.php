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
        Schema::create('affliliate_link_url', function (Blueprint $table) {
            $table->foreignId('affliliate_link_id')
                ->constrained('affliliate_links')
                ->cascadeOnDelete();
            $table->foreignId('url_id')
                ->constrained('urls')
                ->cascadeOnDelete();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_updated_entry')->default(false);
            $table->unique(['affliliate_link_id', 'url_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affliliate_link_url');
    }
};
