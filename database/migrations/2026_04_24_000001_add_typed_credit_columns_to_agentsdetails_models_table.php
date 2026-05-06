<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agentsdetails_models', function (Blueprint $table) {
            // ── Per-type credit counters (agent / parent-agent rows) ───────────
            $table->unsignedInteger('private_allocated')->default(0)->after('noused');
            $table->unsignedInteger('private_used')->default(0)->after('private_allocated');
            $table->unsignedInteger('commercial_allocated')->default(0)->after('private_used');
            $table->unsignedInteger('commercial_used')->default(0)->after('commercial_allocated');

            // ── Per-type pool (parent-agent rows) ─────────────────────────────
            $table->unsignedInteger('pool_private_size')->default(0)->after('pool_used');
            $table->unsignedInteger('pool_private_used')->default(0)->after('pool_private_size');
            $table->unsignedInteger('pool_commercial_size')->default(0)->after('pool_private_used');
            $table->unsignedInteger('pool_commercial_used')->default(0)->after('pool_commercial_size');

            // ── Per-type subagent individual allocation ────────────────────────
            $table->unsignedInteger('subcreditassigned_private')->default(0)->after('subcreditused');
            $table->unsignedInteger('subcreditused_private')->default(0)->after('subcreditassigned_private');
            $table->unsignedInteger('subcreditassigned_commercial')->default(0)->after('subcreditused_private');
            $table->unsignedInteger('subcreditused_commercial')->default(0)->after('subcreditassigned_commercial');

            // ── Per-type subagent pool caps ────────────────────────────────────
            $table->unsignedInteger('pool_cap_private')->default(0)->after('pool_cap_used');
            $table->unsignedInteger('pool_cap_used_private')->default(0)->after('pool_cap_private');
            $table->unsignedInteger('pool_cap_commercial')->default(0)->after('pool_cap_used_private');
            $table->unsignedInteger('pool_cap_used_commercial')->default(0)->after('pool_cap_commercial');
        });
    }

    public function down(): void
    {
        Schema::table('agentsdetails_models', function (Blueprint $table) {
            $table->dropColumn([
                'private_allocated', 'private_used',
                'commercial_allocated', 'commercial_used',
                'pool_private_size', 'pool_private_used',
                'pool_commercial_size', 'pool_commercial_used',
                'subcreditassigned_private', 'subcreditused_private',
                'subcreditassigned_commercial', 'subcreditused_commercial',
                'pool_cap_private', 'pool_cap_used_private',
                'pool_cap_commercial', 'pool_cap_used_commercial',
            ]);
        });
    }
};
