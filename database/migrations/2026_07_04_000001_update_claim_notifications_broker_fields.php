<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claim_notifications', function (Blueprint $table) {
            // Allow broker submissions where claimant contact is not yet known
            $table->string('claimant_email')->nullable()->change();
            $table->string('claimant_phone')->nullable()->change();

            // Broker-specific fields
            $table->boolean('is_third_party')->default(false)->after('claimant_phone');
            $table->string('incident_time', 5)->nullable()->after('incident_date');   // HH:MM
            $table->string('incident_location')->nullable()->after('incident_time');
        });
    }

    public function down(): void
    {
        Schema::table('claim_notifications', function (Blueprint $table) {
            $table->dropColumn(['is_third_party', 'incident_time', 'incident_location']);
            $table->string('claimant_email')->nullable(false)->change();
            $table->string('claimant_phone')->nullable(false)->change();
        });
    }
};
