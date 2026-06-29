<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broker_ticket_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('broker_ticket_messages')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedInteger('size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broker_ticket_attachments');
    }
};
