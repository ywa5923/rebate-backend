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
            $table->string('logo',100)->nullable();
            $table->string('favicon',100)->nullable();
            $table->string('trading_name',256);
            $table->string('home_url',256);
            $table->decimal("overall_rating",3,1)->nullable();
            $table->decimal("user_rating",3,1)->nullable();
            $table->string('support_options',500)->nullable();
            $table->string("account_type")->nullable();
            $table->string("trading_instruments")->nullable();
            $table->string('account_currencies',500)->nullable();
            $table->string('language')->nullable();
            $table->string("default_language",50)->nullable();
            $table->foreignId('broker_type_id')
            ->constrained();
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
