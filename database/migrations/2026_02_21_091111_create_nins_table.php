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
        Schema::create('nins', function (Blueprint $table) {
            $table->id();
            $table->string('policyno');
            $table->string('insuredname');
            $table->string('eliteid');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('nin')->nullable();
            $table->boolean('ninverified')->default(false);
            $table->string('ninphone')->nullable();
            $table->date('nindob')->nullable();
            $table->string('ningender')->nullable();
            $table->string('ninphone')->nullable();
            $table->string('ninlname')->nullable();
            $table->string('ninmname')->nullable();
            $table->string('ninfname')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nins');
    }
};
