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
            $table->string('title');
            $table->string('slug');
            $table->string('group_name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_invariant')->default(true);
            $table->foreignId('parent_id')->nullable()->constrained('matrix_headers')->nullOnDelete();
            $table->foreignId('form_type_id')->nullable()->constrained('form_types')->nullOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->foreignId('matrix_id')->nullable()->constrained('matrices')->nullOnDelete();
            $table->foreignId('broker_id')->nullable()->constrained('brokers')->nullOnDelete();
          //  $table->unique(['slug', 'broker_id', 'matrix_id'], 'unique_slug_broker_matrix');
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
