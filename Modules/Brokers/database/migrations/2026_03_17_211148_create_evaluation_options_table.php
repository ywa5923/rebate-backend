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
        Schema::create('evaluation_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_rule_id')->constrained()->onDelete('cascade');
            $table->string('option_label'); // ex: "Allowed", "Not Allowed"
            $table->string('option_value'); // ex: "allowed", "not allowed"
            $table->string('description')->nullable(); 
            $table->boolean('is_getter')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_options');
    }
};
