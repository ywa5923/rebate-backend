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
        Schema::create('account_types', function (Blueprint $table) {
            $table->id();
            // $table->string('name');
            // $table->enum('broker_type', ['broker', 'crypto', 'prop_firm'])->default('broker');
            // $table->decimal('commission_value', 10, 5)->nullable();
            // $table->decimal('commission_value_p', 10, 5)->nullable();
            // $table->string('commission_unit')->nullable();
            // $table->string('commission_unit_p')->nullable();
            // $table->string('execution_model')->nullable();
            // $table->string('execution_model_p')->nullable();
            // $table->string('max_leverage')->nullable();
            // $table->string('max_leverage_p')->nullable();
            // $table->string('spread_type')->nullable();
            // $table->string('spread_type_p')->nullable();
            // $table->string('spread_url')->nullable();
            // $table->string('spread_url_p')->nullable();
            // $table->string('min_deposit_value')->nullable();
            // $table->string('min_deposit_unit')->nullable();
            // $table->string('min_deposit_value_p')->nullable();
            // $table->string('min_deposit_unit_p')->nullable();

            // $table->string('min_trade_size_value')->nullable();
            // $table->string('min_trade_size_unit')->nullable();
            // $table->string('min_trade_size_value_p')->nullable();
            // $table->string('min_trade_size_unit_p')->nullable();

            // $table->string('stopout_level_value')->nullable();
            // $table->string('stopout_level_unit')->nullable();
            // $table->string('stopout_level_value_p')->nullable();
            // $table->string('stopout_level_unit_p')->nullable();

            // $table->boolean('trailing_stops')->default(false);
            // $table->boolean('trailing_stops_p')->default(false);
            // $table->boolean('allow_scalping')->default(false);
            // $table->boolean('allow_scalping_p')->default(false);
            // $table->boolean('allow_hedging')->default(false);
            // $table->boolean('allow_hedging_p')->default(false);
            // $table->boolean('allow_news_trading')->default(false);
            // $table->boolean('allow_news_trading_p')->default(false);
            // $table->boolean('allow_cent_accounts')->default(false);
            // $table->boolean('allow_cent_accounts_p')->default(false);
            // $table->boolean('allow_islamic_accounts')->default(false);   
            // $table->boolean('allow_islamic_accounts_p')->default(false);

            // $table->string('crypto_trading_instruments')->nullable();
            // $table->string('crypto_trading_instruments_p')->nullable();
            // $table->string('crypto_currencies')->nullable();
            // $table->string('crypto_currencies_p')->nullable();
            
            // $table->string('order_types')->nullable();
            // $table->string('order_types_p')->nullable();

            // $table->boolean('is_active')->default(true);
            // $table->integer('order')->default(0);
            // $table->boolean('is_invariant')->default(true);
            // $table->enum("status",["published","pending","rejected"])->default("published");
            // $table->text("status_reason",1000)->nullable();
            // $table->foreignId('broker_id')->constrained('brokers');
            // $table->foreignId('zone_id')->nullable()->constrained('zones');
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acount_types');
    }
};
