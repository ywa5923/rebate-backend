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
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->string('matrix_name');
            $table->boolean('is_placeholder')->default(false);
            $table->foreignId('matrix_id')->constrained('matrices');
            $table->foreignId('challenge_category_id')->constrained('challenge_categories');
            $table->foreignId('challenge_step_id')->constrained('challenge_steps');
            $table->foreignId('challenge_amount_id')->constrained('challenge_amounts');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};
