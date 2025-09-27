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
        Schema::create('cost_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('value')->nullable();
            $table->string('public_value')->nullable();
            $table->string('previous_value')->nullable();
            $table->boolean('is_updated_entry')->default(false);
            $table->foreignId('broker_id')->constrained('brokers')->onDelete('cascade');
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->foreignId('challenge_id')->constrained('challenges')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cost_discounts');
    }
};
