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
        Schema::table('magic_links', function (Blueprint $table) {
            $table->foreignId('broker_team_user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('user_type')->default('broker'); // 'broker' or 'team_user'
            
            $table->index(['broker_team_user_id', 'expires_at']);
            $table->index(['user_type', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('magic_links', function (Blueprint $table) {
            $table->dropForeign(['broker_team_user_id']);
            $table->dropColumn(['broker_team_user_id', 'user_type']);
        });
    }
};