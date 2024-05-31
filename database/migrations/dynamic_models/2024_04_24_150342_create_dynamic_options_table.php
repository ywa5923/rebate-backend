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
        Schema::create('dynamic_options', function (Blueprint $table) {
            $table->id();
            $table->string('name',100);
            $table->string('description',500)->nullable();
            $table->enum('type',['int','float','double','numeric','string','text','radio','checkbox']);
           
            $table->json('metadata')->nullable();
           
            $table->foreignId('category_id')
            ->constrained(table: 'dynamic_options_categories');
            
            $table->timestamps();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dynamic_options');
    }
};
