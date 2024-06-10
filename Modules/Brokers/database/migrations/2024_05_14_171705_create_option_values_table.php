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
        Schema::create('option_values', function (Blueprint $table) {
            $table->id();
            $table->string("option_slug");
            $table->text("value");
            $table->boolean("status")->default(1);//de bagat si in broker options;
            $table->string("status_message")->nullable();
            $table->boolean("default_loading")->default(1);
            $table->string("unit",100)->nullable();
            $table->string("metadata",1000)->nullable();
            $table->foreignId("broker_id")->constrained();
            $table->foreignId("broker_option_id")->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('option_values');
    }
};
