<?php

declare(strict_types=1);

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
        Schema::table('transactions', function (Blueprint $table) {
            // Index on description for faster LIKE queries
            $table->index('description', 'transactions_description_index');

            // Composite index for account-specific description searches
            $table->index(['account_id', 'description'], 'transactions_account_description_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_description_index');
            $table->dropIndex('transactions_account_description_index');
        });
    }
};
