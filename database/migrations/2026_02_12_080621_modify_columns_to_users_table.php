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
        Schema::table('agentsdetails_models', function (Blueprint $table) {
            //
            $table->integer('puid')->nullable();
            $table->integer('subcreditassigned')->default(0);
            $table->integer('subcreditused')->default(0);
            $table->boolean('issubagent')->default(false);
            $table->boolean('canregistersubagent')->default(false);
            $table->boolean('isactive')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agentsdetails_models', function (Blueprint $table) {
            //
            $table->dropColumn('puid');
            $table->dropColumn('subcreditassigned');
            $table->dropColumn('subcreditused');        
            $table->dropColumn('issubagent');
            $table->dropColumn('canregistersubagent');
            $table->dropColumn('isactive');
            
        });
    }
};
