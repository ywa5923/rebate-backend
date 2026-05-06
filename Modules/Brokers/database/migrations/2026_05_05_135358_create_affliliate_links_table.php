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
        Schema::create('affliliate_links', function (Blueprint $table) {
            $table->id();
            $table->string('affiliate_type');
            $table->string('name');
            $table->string('public_name')->nullable();
            $table->string('previous_name')->nullable();
            $table->string('url');
            $table->string('public_url')->nullable();
            $table->string('previous_url')->nullable();
            $table->string('currency')->nullable();
            $table->string('previous_currency')->nullable();
            $table->string('public_currency')->nullable();
            $table->boolean('is_updated_entry')->default(false);
            $table->boolean('is_master_link')->default(false);
            $table->foreignId('account_type_id')->nullable()->constrained('account_types');
            $table->foreignId('broker_id')->constrained('brokers');
            $table->foreignId('zone_id')->nullable()->constrained('zones');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affliliate_links');
    }
};
