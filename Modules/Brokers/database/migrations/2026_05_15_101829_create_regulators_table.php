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
            $table->string('name');
            $table->string('acronym');
            $table->string('country')->nullable();
            $table->string('country_code')->nullable();
            $table->string('zone')->nullable();
            $table->string('tier_classification')->nullable();
            $table->decimal('rating', 3, 2)->nullable();
            $table->string('investor_protection_scheme')->nullable();
            $table->string('compensation_scheme')->nullable();
            $table->string('retail_leverage_restrictions')->nullable();
            $table->string('website')->nullable();
            $table->unsignedSmallInteger('year_established')->nullable();
            $table->string('jurisdiction_type')->nullable();
            $table->text('notes')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->nullable();
            $table->text('status_reason')->nullable();
            $table->boolean('is_invariant')->default(true);
            $table->foreignId('zone_id')->nullable()->constrained('zones');
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
