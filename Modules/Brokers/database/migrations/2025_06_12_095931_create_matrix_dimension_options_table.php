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
        Schema::create('matrix_dimension_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matrix_id')->constrained('matrices')->onDelete('cascade');
            $table->foreignId('broker_id')->constrained('brokers')->onDelete('cascade');
            //$table->foreignId('matrix_header_id')->constrained('matrix_headers')->onDelete('cascade');
            $table->foreignId('matrix_dimension_id')->constrained('matrix_dimensions')->onDelete('cascade');
            $table->foreignId('option_id')->constrained('matrix_headers')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matrix_dimension_options');
    }
};
