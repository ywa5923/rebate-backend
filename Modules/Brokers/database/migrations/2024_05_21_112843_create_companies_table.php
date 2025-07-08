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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string("name",250);
            $table->string("name_p",250)->nullable();
            $table->text("licence_number")->nullable();
            $table->text("licence_number_p")->nullable();
            $table->string("crypto_categories")->nullable();
            $table->string("crypto_categories_p")->nullable();
            $table->text("banner")->nullable();
            $table->text("banner_p")->nullable();
            $table->text("description")->nullable();
            $table->text("description_p")->nullable();
            $table->string("year_founded")->nullable();
            $table->string("year_founded_p")->nullable();
            $table->string("employees")->nullable();
            $table->string("employees_p")->nullable();
            $table->text("headquarters")->nullable();
            $table->text("headquarters_p")->nullable();
            $table->text("offices")->nullable();
            $table->text("offices_p")->nullable();
            $table->enum("status",["published","pending","rejected"])->default("published");
            $table->text("status_reason",1000)->nullable();
            $table->boolean('is_invariant')->default(true);
            $table->foreignId("broker_id")->constrained("brokers");
            $table->foreignId('zone_id')->nullable()->constrained('zones');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
