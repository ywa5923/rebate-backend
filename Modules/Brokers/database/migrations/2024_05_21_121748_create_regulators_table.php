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
        Schema::create('regulators', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("abreviation")->nullable();
            $table->string("country")->nullable();
            $table->string("country_p")->nullable();
            $table->text("description")->nullable();
            $table->text("description_p")->nullable();
 
            $table->decimal("rating",3,2)->nullable();
            $table->decimal("rating_p",3,2)->nullable();

            $table->string("capitalization",1000)->nullable();
            $table->string("capitalization_p",1000)->nullable();

            $table->string("segregated_clients_money")->nullable();
            $table->string("segregated_clients_money_p")->nullable();
            $table->string("deposit_compensation_scheme")->nullable();
            $table->string("deposit_compensation_scheme_p")->nullable();
            $table->string("negative_balance_protection")->nullable();
            $table->string("negative_balance_protection_p")->nullable();
            $table->boolean("rebates")->nullable();
            $table->boolean("rebates_p")->nullable();
            $table->boolean("enforced")->nullable();
            $table->boolean("enforced_p")->nullable();
            $table->text("max_leverage_p")->nullable();
            $table->text("max_leverage")->nullable();
            $table->string("website",500)->nullable();
            $table->string("website_p",500)->nullable();
            $table->enum("status",["published","pending","rejected"])->default("published");
            $table->text("status_reason",1000)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regulators');
    }
};
