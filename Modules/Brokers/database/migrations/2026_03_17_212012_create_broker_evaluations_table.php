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
        Schema::create('broker_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broker_id')->constrained();
            $table->foreignId('evaluation_rule_id')->constrained();
            $table->unique(['broker_id','evaluation_rule_id','zone_id']);
            $table->foreignId('evaluation_option_id')->nullable()->constrained('evaluation_options'); 
            $table->foreignId('public_evaluation_option_id')->nullable()->constrained('evaluation_options');
            $table->foreignId('previous_evaluation_option_id')->nullable()->constrained('evaluation_options');
            $table->text('details')->nullable();
            $table->text('public_details')->nullable();
            $table->text('previous_details')->nullable();
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
        Schema::dropIfExists('broker_evaluations');
    }
};
