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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string("name",250);
            $table->string("name_p",250)->nullable();
            $table->text("licence_number")->nullable();
            $table->string("licence_number_p",250)->nullable();
            $table->text("banner")->nullable();
            $table->text("banner_p")->nullable();
            $table->text("description",250)->nullable();
            $table->text("description_p",250)->nullable();
            $table->string("year_founded")->nullable();
            $table->string("year_founded_p")->nullable();
            $table->string("employees")->nullable();
            $table->string("employees_p")->nullable();
            $table->string("headquarters",1000)->nullable();
            $table->string("headquarters_p",1000)->nullable();
            $table->string("offices",1000)->nullable();
            $table->string("offices_p",1000)->nullable();
            $table->enum("status",["published","pending","rejected"])->default("published");
            $table->text("status_reason",1000)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
