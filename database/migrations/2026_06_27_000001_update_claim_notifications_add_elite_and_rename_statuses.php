<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claim_notifications', function (Blueprint $table) {
            $table->string('elite_claim_no')->nullable()->unique()->after('status');
        });

        // Rename legacy status values
        DB::table('claim_notifications')->where('status', 'submitted')->update(['status'  => 'received']);
        DB::table('claim_notifications')->where('status', 'acknowledged')->update(['status' => 'registered']);
    }

    public function down(): void
    {
        DB::table('claim_notifications')->where('status', 'received')->update(['status'    => 'submitted']);
        DB::table('claim_notifications')->where('status', 'registered')->update(['status'  => 'acknowledged']);

        Schema::table('claim_notifications', function (Blueprint $table) {
            $table->dropColumn('elite_claim_no');
        });
    }
};
