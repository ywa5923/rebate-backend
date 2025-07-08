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
            $table->text("public_value")->nullable();
            $table->boolean("status")->default(1);//de bagat si in broker options;
            $table->string("status_message")->nullable();
            $table->boolean("default_loading")->default(1);
            $table->string("type",100)->nullable();
            $table->json('metadata')->nullable();
          //  $table->string('zone_code',200)->nullable();
            $table->boolean('is_invariant')->default(1);
            $table->boolean('delete_by_system')->default(0);
            $table->foreignId("broker_id")->constrained();
            $table->foreignId("broker_option_id")->constrained();
            $table->timestamps();
            $table->foreignId('zone_id')->nullable()->constrained('zones');

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
