<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update transaction statuses based on their date
        // Future dates = planned, today/past dates = entered
        DB::statement("
            UPDATE transactions
            SET status = CASE
                WHEN date(transaction_date) > date('now') THEN 'planned'
                ELSE 'entered'
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset all transactions to the previous default status 'entered'
        DB::table('transactions')->update(['status' => 'entered']);
    }
};
