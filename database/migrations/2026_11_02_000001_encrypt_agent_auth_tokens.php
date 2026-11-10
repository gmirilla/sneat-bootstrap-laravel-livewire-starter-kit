<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Elite auth tokens were stored in plaintext. Widen the column (ciphertext is longer than
 * 255 chars) and encrypt existing values. Idempotent: values that already decrypt are skipped.
 * NOTE: ciphertext is bound to APP_KEY - back it up and never rotate it without re-encrypting.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agentsdetails_models', function (Blueprint $table) {
            $table->text('auth_token')->nullable()->change();
        });

        DB::table('agentsdetails_models')->whereNotNull('auth_token')->orderBy('id')->each(function ($row) {
            if ($row->auth_token === '' || $this->isEncrypted($row->auth_token)) {
                return;
            }

            DB::table('agentsdetails_models')->where('id', $row->id)
                ->update(['auth_token' => Crypt::encryptString($row->auth_token)]);
        });
    }

    public function down(): void
    {
        DB::table('agentsdetails_models')->whereNotNull('auth_token')->orderBy('id')->each(function ($row) {
            if ($this->isEncrypted($row->auth_token)) {
                DB::table('agentsdetails_models')->where('id', $row->id)
                    ->update(['auth_token' => Crypt::decryptString($row->auth_token)]);
            }
        });

        Schema::table('agentsdetails_models', function (Blueprint $table) {
            $table->string('auth_token')->nullable()->change();
        });
    }

    private function isEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
};
