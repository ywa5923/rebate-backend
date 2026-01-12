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
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('subject_type')->nullable(); // Modules\Auth\Models\BrokerTeamUser or Modules\Auth\Models\PlatformUser
            $table->unsignedBigInteger('subject_id')->nullable(); // broker_team_user_id or platform_user_id
            $table->enum('permission_type', ['broker', 'country', 'zone', 'seo', 'translator', 'super-admin']);
            $table->unsignedBigInteger('resource_id')->nullable(); // For specific broker IDs
            $table->string('resource_value')->nullable(); // For countries, zones, broker types
            $table->enum('action', ['view', 'edit', 'delete', 'manage']);
            $table->json('metadata')->nullable(); // Additional permission data
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['subject_type', 'subject_id']);
            $table->index(['permission_type', 'resource_id']);
            $table->index(['permission_type', 'resource_value']);
            $table->index(['action', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};