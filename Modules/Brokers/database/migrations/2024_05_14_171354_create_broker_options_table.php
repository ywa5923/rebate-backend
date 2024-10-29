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
            $table->string('data_type',100);
            $table->string('form_type',200);
            $table->json('meta_data')->nullable();
            $table->boolean('for_crypto');
            $table->boolean('for_brokers');
            $table->boolean('for_props');
            $table->boolean('required');
            $table->boolean('load_in_dropdown')->default(1);
            $table->boolean('default_loading')->nullable();
            $table->integer('default_loading_position')->default(1)->nullable();
            $table->integer('dropdown_position')->default(1);
            $table->boolean('publish')->default(1);
            $table->integer('position')->default(1);
            $table->boolean("allow_sorting")->default(0);
            $table->string("default_language",50);
            $table->foreignId("option_category_id")->constrained("option_categories");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('broker_options');
    }
};
