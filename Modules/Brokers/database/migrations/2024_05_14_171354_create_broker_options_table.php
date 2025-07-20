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
            $table->string('slug',100);
            $table->string('applicable_for',255)->nullable();
            $table->string('data_type',100);
            $table->string('form_type',200);
            $table->json('meta_data')->nullable();
            $table->boolean('for_crypto');
            $table->boolean('for_brokers');
            $table->boolean('for_props');
            $table->boolean('required');
            $table->string('placeholder',100)->nullable();
            $table->string('tooltip',500)->nullable();
            $table->string("min_constraint",100)->nullable();
            $table->string("max_constraint",100)->nullable();
            $table->boolean('load_in_dropdown')->default(1);
            $table->boolean('default_loading')->nullable();
            $table->integer('default_loading_position')->default(1)->nullable();
            $table->integer('dropdown_position')->default(1);
            $table->integer('category_position')->default(1);
            $table->boolean('publish')->default(1);
            $table->integer('position')->default(1);
            $table->boolean("allow_sorting")->default(0);
            $table->string("default_language",50);
            $table->foreignId("option_category_id")->constrained("option_categories");
            $table->foreignId("dropdown_category_id")->nullable()->constrained("dropdown_categories");
            $table->timestamps();
        });
    }//[{"label": "Lots", "value": "lots"}, {"label": "Pips", "value": "pips"}]
    //[{"label": "English", "value": "en"}, {"label": "Romania", "value": "ro"}]

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('broker_options');
    }
};
