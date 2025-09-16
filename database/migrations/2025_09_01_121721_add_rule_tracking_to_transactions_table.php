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
            // Track which rule automatically categorized this transaction
            $table->unsignedBigInteger('applied_rule_id')->nullable()->after('category_id');
            $table->timestamp('auto_categorized_at')->nullable()->after('applied_rule_id');

            // Add foreign key constraint
            $table->foreign('applied_rule_id')
                  ->references('id')
                  ->on('category_rules')
                  ->onDelete('set null');

            // Add indexes for performance
            $table->index('applied_rule_id');
            $table->index('auto_categorized_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['applied_rule_id']);

            // Drop indexes
            $table->dropIndex(['applied_rule_id']);
            $table->dropIndex(['auto_categorized_at']);

            // Drop columns
            $table->dropColumn(['applied_rule_id', 'auto_categorized_at']);
        });
    }
};
