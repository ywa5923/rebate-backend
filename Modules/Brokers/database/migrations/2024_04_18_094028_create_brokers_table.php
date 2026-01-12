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
        Schema::create('brokers', function (Blueprint $table) {
            $table->id();
            $table->string('registration_language',50)->nullable();
            $table->string("registration_zone",50)->nullable();
            $table->boolean("is_active")->default(true);
            $table->foreignId('broker_type_id')
            ->constrained();
            $table->foreignId('country_id')->nullable()->constrained();
            $table->foreignId('zone_id')->nullable()->constrained();
            $table->timestamps();
            
        });
    }

    
//logo=>logo,favicon,trading_name=>name,overall_rating,user_rating=>rating,support_options=>"!support_options"
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brokers');
    }
};
