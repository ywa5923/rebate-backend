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
        Schema::create('urls', function (Blueprint $table) {
            $table->id();
            $table->string("url_type");
            $table->string("url",500);
            $table->string("name",500);
            $table->string("slug",500);
            $table->integer("category_position")->nullable();
            $table->string("description",500)->nullable(); 
            $table->foreignId("option_category_id")->constrained("option_categories");
            $table->foreignId("broker_id")->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('urls');
    }
};

//url types
//home
//live-trading-account
//partner-account
//demo-trading-account
//trading-platform
//web-platform
//mobile-platform
//spread-value
//comission-value
//rollover-value
