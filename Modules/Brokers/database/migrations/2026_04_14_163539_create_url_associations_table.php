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
        Schema::create('url_associations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('url_id')->constrained('urls');
            $table->foreignId('associated_url_id')->constrained('urls');
            $table->string('association_type')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_updated_entry')->default(false);
            $table->foreignId('zone_id')->nullable()->constrained('zones');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('url_associations');
    }
};
