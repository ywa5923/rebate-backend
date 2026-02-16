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
        Schema::create('option_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name',100);
            $table->string('slug',100)->unique();
            $table->string('description',500)->nullable();
            $table->string('icon',100)->nullable();
            $table->integer('position')->default(1);
            $table->integer('for_brokers')->default(1);
            $table->integer('for_crypto')->default(1);
            $table->integer('for_props')->default(1);
            $table->foreignId('zone_id')->nullable()->constrained('zones');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('option_categories');
    }
};
