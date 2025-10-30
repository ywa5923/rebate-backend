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
        Schema::create('platform_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
           // $table->string('password')->nullable(); // Can be null if only magic link login
            $table->string('role')->default('admin'); // e.g., 'country_admin', 'global_admin'
            $table->boolean('is_active')->default(true);
           // $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            //$table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_users');
    }
};