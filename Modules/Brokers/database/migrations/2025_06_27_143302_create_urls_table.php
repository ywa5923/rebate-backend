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
            $table->nullableMorphs('urlable');
            $table->string("url_type");
            $table->string("url",500);
            $table->string("public_url",500)->nullable();
            $table->string("old_url",500)->nullable();
            $table->boolean("is_updated_entry")->default(false);
            $table->string("url_p",500)->nullable();
            $table->string("name",500);
            $table->string("name_p",500)->nullable();
            $table->string("slug",500);
            $table->boolean("is_invariant")->default(true);
            $table->integer("category_position")->nullable();
            $table->string("description",500)->nullable(); 
            $table->enum("status",["published","pending","rejected"])->default("published");
            $table->text("status_reason",1000)->nullable();
            $table->foreignId("option_category_id")->nullable()->constrained("option_categories");
            $table->foreignId("broker_id")->constrained();
            $table->foreignId("zone_id")->nullable()->constrained();
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
