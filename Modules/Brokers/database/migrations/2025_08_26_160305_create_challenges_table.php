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
            $table->string('name')->nullable();
            $table->boolean('is_placeholder')->default(false);
           
           // $table->foreignId('matrix_id')->constrained('matrices');
            $table->foreignId('challenge_category_id')->constrained('challenge_categories')->nullable()->nullOnDelete();
            $table->foreignId('challenge_step_id')->constrained('challenge_steps')->nullable()->nullOnDelete();
            $table->foreignId('challenge_amount_id')->constrained('challenge_amounts')->nullable()->nullOnDelete();
            //FOR PLACEHOLDER DATA, BROKER ID AND ZONE ID ARE NULL
            $table->foreignId('broker_id')->constrained('brokers')->nullable()->nullOnDelete();
            $table->foreignId('zone_id')->constrained('zones')->nullable()->nullOnDelete();
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
