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
        Schema::table('category_rules', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('category_id')->constrained()->cascadeOnDelete();
            $table->index('account_id');
            $table->index(['account_id', 'category_id', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('category_rules', function (Blueprint $table) {
            $table->dropIndex(['account_id', 'category_id', 'priority']);
            $table->dropIndex(['account_id']);
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });
    }
};
