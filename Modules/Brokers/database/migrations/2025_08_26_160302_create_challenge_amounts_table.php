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
        Schema::create('challenge_amounts', function (Blueprint $table) {
            $table->id();
            $table->string('amount');
            $table->string('currency');
            $table->integer('order')->default(0);
            $table->foreignId('challenge_category_id')->constrained('challenge_categories')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_amounts');
    }
};
