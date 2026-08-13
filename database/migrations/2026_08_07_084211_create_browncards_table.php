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
        Schema::create('browncards', function (Blueprint $table) {
            $table->id();
            $table->integer('policyid')->nullable();
            $table->integer('stringid')->nullable();
            $table->string('policynumber')->nullable();
            $table->string('regno')->nullable();
            $table->boolean('elitesuccess')->default(false);
            $table->string('elitemsg')->nullable();
            $table->string('browncardnumber')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('browncards');
    }
};
