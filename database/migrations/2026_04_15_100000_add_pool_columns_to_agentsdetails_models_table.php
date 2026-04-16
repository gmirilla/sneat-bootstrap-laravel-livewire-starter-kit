<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agentsdetails_models', function (Blueprint $table) {
            // ── Parent-agent pool columns ──────────────────────────────────
            $table->boolean('pool_enabled')->default(false)->after('canregistersubagent');
            $table->unsignedInteger('pool_size')->default(0)->after('pool_enabled');
            $table->unsignedInteger('pool_used')->default(0)->after('pool_size');

            // ── Per-subagent cap columns (only read when parent pool_enabled) ─
            $table->unsignedInteger('pool_cap')->default(0)->after('pool_used');      // 0 = unlimited
            $table->unsignedInteger('pool_cap_used')->default(0)->after('pool_cap');
        });
    }

    public function down(): void
    {
        Schema::table('agentsdetails_models', function (Blueprint $table) {
            $table->dropColumn(['pool_enabled', 'pool_size', 'pool_used', 'pool_cap', 'pool_cap_used']);
        });
    }
};
