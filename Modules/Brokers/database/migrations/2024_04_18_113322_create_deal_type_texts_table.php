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
        Schema::create('deal_type_texts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_type_id')->constrained();
            $table->string('language',5);
            $table->string('name',256);
            $table->text('description');
            $table->text('example');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deal_type_texts');
    }
};
