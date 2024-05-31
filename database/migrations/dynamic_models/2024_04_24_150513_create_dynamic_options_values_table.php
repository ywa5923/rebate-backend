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
        Schema::create('dynamic_options_values', function (Blueprint $table) {
            $table->id();
            $table->morphs('dynamicable');
            $table->string('option_name',100);
            $table->foreignId('dynamic_option_id')
            ->constrained();
            $table->text('value'); 
            $table->timestamps();
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dynamic_options_values');
    }
};
