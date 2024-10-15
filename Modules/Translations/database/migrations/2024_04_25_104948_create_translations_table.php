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
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('translationable');
            $table->string('language_code',10)->nullable()->default(null);
            $table->string('property',100)->nullable();
            $table->text('value')->nullable();
            $table->json('metadata')->nullable();
            $table->string('translation_type',50)->default("property");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
