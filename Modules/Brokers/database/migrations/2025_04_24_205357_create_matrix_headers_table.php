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
        Schema::create('matrix_headers', function (Blueprint $table) {
            $table->id();
            $table->enum('type',['column','row']);
            $table->string('title')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_invariant')->default(true);
            $table->foreignId('parent_id')->nullable()->constrained('matrix_headers')->nullOnDelete();
            $table->foreignId('form_type_id')->nullable()->constrained('form_types')->nullOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->foreignId('matrix_id')->constrained('matrices');
            $table->foreignId('broker_id')->nullable()->constrained('brokers')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matrix_headers');
    }
};
