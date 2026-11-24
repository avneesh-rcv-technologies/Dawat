<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->string('refund_number', 32)->nullable()->unique();
        });
        DB::table('refunds')->orderBy('id')->chunkById(100, function ($refunds) {
            foreach ($refunds as $refund) {
                DB::transaction(function () use ($refund) {
                    $date = Carbon::parse($refund->created_at)->format('Ymd');
                    do {
                        $number = 'RFN-'.$date.'-'.Str::upper(Str::random(8));
                    } while (DB::table('refunds')->where('refund_number', $number)->exists());
                    DB::table('refunds')->where('id', $refund->id)->update(['refund_number' => $number]);
                    if (! $refund->transaction_id) {
                        return;
                    }
                    $transaction = DB::table('transactions')->where('id', $refund->transaction_id)->lockForUpdate()->first();
                    if (! $transaction || $transaction->type !== 'refund') {
                        return;
                    }
                    $metadata = json_decode($transaction->metadata ?? '{}', true) ?: [];
                    $metadata['refund_number'] = $number;
                    $updates = [];
                    // Replace only the generated UUID references, preserving the old value for audit.
                    if (preg_match('/^RFN-[0-9A-F]{8}(?:-[0-9A-F]{4}){3}-[0-9A-F]{12}$/i', $transaction->transaction_number)) {
                        do {
                            $short = 'TXN-'.$date.'-'.Str::upper(Str::random(8));
                        } while (DB::table('transactions')->where('transaction_number', $short)->exists());
                        $metadata['previous_transaction_number'] = $transaction->transaction_number;
                        $updates['transaction_number'] = $short;
                    }
                    if (str_starts_with($transaction->notes ?? '', 'Manual refund #'.$refund->id.' — ')) {
                        $updates['notes'] = 'Manual refund '.$number.' — '.$refund->refund_status;
                    }
                    DB::table('transactions')->where('id', $transaction->id)->update($updates + ['metadata' => json_encode($metadata)]);
                });
            }
        });
        Schema::table('refunds', function (Blueprint $table) {
            $table->string('refund_number', 32)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropUnique(['refund_number']);
            $table->dropColumn('refund_number');
        });
        // Existing transaction references remain stable when rolling back this column.
    }
};
