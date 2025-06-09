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
        Schema::create('option_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name',100);
            $table->string('description',500)->nullable();
            $table->string('icon',100)->nullable();
            $table->string('color',100)->nullable();
            $table->string('background_color',100)->nullable();
            $table->string('border_color',100)->nullable();
            $table->string('text_color',100)->nullable();
            $table->string('font_weight',100)->nullable();
            $table->integer('position')->default(1);
            $table->string("default_language",50)->nullable();
            $table->timestamps();
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('option_categories');
    }
};
