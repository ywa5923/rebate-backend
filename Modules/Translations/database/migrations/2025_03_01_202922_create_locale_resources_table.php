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
        Schema::create('locale_resources', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('section');
            $table->string('zone_code')->nullable();
            $table->boolean('is_invariant')->default(0);
            $table->json('json_content');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    } 

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locale_resources');
    }
};
