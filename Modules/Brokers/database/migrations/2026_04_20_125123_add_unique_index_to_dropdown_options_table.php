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
        Schema::table('dropdown_options', function (Blueprint $table) {
            $table->unique(
                ['dropdown_category_id', 'value'],
                'dropdown_options_category_value_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dropdown_options', function (Blueprint $table) {
            $table->dropUnique('dropdown_options_category_value_unique');
        });
    }
};
