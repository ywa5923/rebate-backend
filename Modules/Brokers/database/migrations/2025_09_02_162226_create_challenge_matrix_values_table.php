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
        Schema::create('challenge_matrix_values', function (Blueprint $table) {
            $table->id();
            $table->json('previous_value')->nullable();
            $table->json('value');
            $table->json('previous_public_value')->nullable();
            $table->json('public_value')->nullable();
            $table->boolean('is_updated_entry')->default(false);
            $table->boolean('is_invariant')->default(true);
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->foreignId('challenge_id')->constrained('challenges')->onDelete('cascade');
            $table->foreignId('row_id')->constrained('matrix_headers')->nullable()->onDelete('cascade');
            $table->foreignId('column_id')->constrained('matrix_headers')->nullable()->onDelete('cascade');
            $table->foreignId('broker_id')->constrained('brokers')->nullable()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_matrix_values');
    }
};
