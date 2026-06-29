<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broker_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('broker_id');
            $table->string('policy_no');
            $table->string('subject');
            $table->enum('status', ['open', 'in_progress', 'awaiting_broker', 'resolved', 'closed'])->default('open');
            $table->enum('priority', ['normal', 'high', 'urgent'])->default('normal');
            $table->timestamps();

            $table->index('broker_id');
            $table->index('policy_no');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broker_tickets');
    }
};
