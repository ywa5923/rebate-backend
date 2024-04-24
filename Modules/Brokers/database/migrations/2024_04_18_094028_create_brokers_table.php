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
            $table->enum('broker_type',['broker', 'crypto', 'prop_firm']);
            $table->string('logo',100);
            $table->string('favicon',100);
            $table->foreignId('deal_type_id')->constrained();
            $table->string('trading_name',256);
            $table->string('home_url',256);
            $table->string('live_account_url',256);
            $table->string('partner_account_url',256);
            $table->string('demo_url',256)->nullable();
            $table->string('restricted_countries')->nullable();
            $table->string('restricted_languages')->nullable();
            $table->string('position_home')->nullable();
            $table->string('position_list')->nullable();
            $table->string('position_grid')->nullable();
            $table->string('position_table')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brokers');
    }
};
