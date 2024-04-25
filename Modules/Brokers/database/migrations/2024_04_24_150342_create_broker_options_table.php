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
        Schema::create('broker_options', function (Blueprint $table) {
            $table->id();
            $table->string('name',100);
            $table->string('description',500)->nullable();
            $table->enum('type',['int','float','double','numeric','string','text','radio','checkbox']);
            $table->string('requirements',500)->nullable();
            $table->json('metadata')->nullable();
            $table->integer('position');
            $table->foreignId('broker_type_id')
            ->constrained()
            ->onUpdate('cascade')
            ->onDelete('cascade');

            $table->foreignId('category_id')
            ->constrained(table: 'broker_options_categories');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('broker_options');
    }
};
