<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a human-readable, unique payment_reference (PAY-XXXXXXXX) to the
 * payments table to replace the ad-hoc gateway_reference field as the
 * primary "receipt number" shown to users and admins.
 *
 * Also seeds existing rows with a generated reference so no data is lost.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_reference', 32)
                ->nullable()
                ->unique()
                ->after('gateway_reference')
                ->comment('Human-readable receipt number, e.g. PAY-A1B2C3D4');
        });

        // Back-fill existing rows so the column is never NULL in production
        \DB::table('payments')->whereNull('payment_reference')->orderBy('id')->each(function ($row) {
            \DB::table('payments')
                ->where('id', $row->id)
                ->update([
                    'payment_reference' => 'PAY-' . strtoupper(substr(md5('legacy-' . $row->id . $row->created_at), 0, 8)),
                ]);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('payment_reference');
        });
    }
};
