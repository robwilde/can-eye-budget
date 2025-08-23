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
        Schema::table('accounts', function (Blueprint $table) {
            $table->decimal('credit_limit', 15, 2)->nullable()->after('initial_balance');
            $table->text('description')->nullable()->after('currency');
            $table->boolean('is_visible_in_totals')->default(true)->after('description');
            $table->unsignedBigInteger('account_category_id')->nullable()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['credit_limit', 'description', 'is_visible_in_totals', 'account_category_id']);
        });
    }
};
