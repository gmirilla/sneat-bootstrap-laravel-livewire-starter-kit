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
        Schema::table('policies', function (Blueprint $table) {
            //
            $table->string('paymenttype')->nullable();
            $table->boolean('cancelled')->default(false);
            $table->string('cancellation_reason')->nullable();
            $table->date('cancellation_date')->nullable();
            $table->string('cancellation_uid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            //
            $table->dropColumn('paymenttype');
            $table->dropColumn('cancelled');
            $table->dropColumn('cancellation_reason'); 
            $table->dropColumn('cancellation_date');
            $table->dropColumn('cancellation_uid');
        });
    }
};
