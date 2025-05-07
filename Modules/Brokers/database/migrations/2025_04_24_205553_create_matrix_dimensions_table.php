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
        Schema::create('matrix_dimensions', function (Blueprint $table) {
            $table->id();
            $table->enum('type',['column','row']);
            $table->integer('order');
            $table->boolean('is_invariant')->default(true);
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->foreignId('broker_id')->constrained('brokers')->onDelete('cascade');
            $table->foreignId('matrix_id')->constrained('matrices')->onDelete('cascade');
            $table->foreignId('matrix_header_id')->constrained('matrix_headers')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matrix_dimensions');
    }
};
