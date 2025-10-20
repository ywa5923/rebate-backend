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
        Schema::create('magic_links', function (Blueprint $table) {
            $table->id();
            $table->string('subject_type')->nullable(); // Modules\Auth\Models\BrokerTeamUser or Modules\Auth\Models\PlatformUser
            $table->unsignedBigInteger('subject_id')->nullable(); // broker_team_user_id or platform_user_id
            $table->unsignedBigInteger('context_broker_id')->nullable(); // Optional broker context (analytics/scoping)
            $table->string('token', 64)->unique();
            $table->string('email');
            $table->string('action')->default('login'); // login, registration, password_reset
            $table->json('metadata')->nullable(); // Additional data
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['token', 'expires_at']);
            $table->index(['subject_type', 'subject_id']);
            $table->index(['context_broker_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('magic_links');
    }
};