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
        Schema::create('dynamic_models_dynamic_options', function (Blueprint $table) {
            $table->id();
            $table->integer('position');
            $table->string('model_variant')->nullable();
            $table->string('validation_string',500)->nullable();
            $table->boolean("required");
            $table->boolean("included_by_default");
            $table->foreignId('dynamic_model_id')
            ->constrained();
            $table->foreignId('dynamic_option_id')
            ->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dynamic_models_dynamic_options');
    }
};
