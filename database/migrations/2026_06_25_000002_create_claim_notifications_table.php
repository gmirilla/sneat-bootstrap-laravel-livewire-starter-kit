<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claim_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->string('policy_no');
            $table->string('policy_type');
            $table->date('policy_start');
            $table->date('policy_end');
            $table->string('policy_source'); // local | remote | both
            $table->string('claimant_name');
            $table->string('claimant_email');
            $table->string('claimant_phone');
            $table->date('incident_date');
            $table->text('description');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('submitted'); // submitted | acknowledged | closed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_notifications');
    }
};
