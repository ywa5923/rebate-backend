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
        Schema::create('broker_regulator', function (Blueprint $table) {
            $table->id();
            $table->foreignId("broker_id")->constrained();
            $table->foreignId("regulator_id")->constrained();
            $table->string('zone_code',200)->nullable()->default(null);
            $table->boolean('is_invariant')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('broker_regulator');
    }
};
