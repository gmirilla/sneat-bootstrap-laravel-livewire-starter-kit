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
        Schema::create('paystacktransactions', function (Blueprint $table) {
            $table->id();
            $table->string('ref_id')->unique();
            $table->integer('policy_id');
            $table->string('policyno')->nullable();
            $table->string('email')->nullable();
            $table->decimal('amount', 10,2);
            $table->string('access_code')->nullable();
            $table->string('reference_code')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paystacktransactions');
    }
};
