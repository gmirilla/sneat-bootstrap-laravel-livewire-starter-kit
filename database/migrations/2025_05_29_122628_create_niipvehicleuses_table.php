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
        Schema::create('niipvehicleuses', function (Blueprint $table) {
            $table->id();
            $table->integer(('niipuseid'))->unique()->comment('NIIP Unique identifier for the NIIP vehicle Use');
            $table->string('usename')->comment('Name of the usename');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('niipvehicleuses');
    }
};
