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
        Schema::create('form_type_form_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('form_item_id')->constrained()->onDelete('cascade');
            // Optional: Add unique constraint to prevent duplicates
            $table->unique(['form_type_id', 'form_item_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_type_form_item');
    }
};
