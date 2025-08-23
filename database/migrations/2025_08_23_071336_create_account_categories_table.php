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
        Schema::create('account_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('display_in_list')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'name']);
            $table->index(['user_id', 'sort_order']);
        });

        // Add foreign key constraint to accounts table after account_categories table is created
        Schema::table('accounts', function (Blueprint $table) {
            $table->foreign('account_category_id')->references('id')->on('account_categories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraint from accounts table first
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign(['account_category_id']);
        });

        Schema::dropIfExists('account_categories');
    }
};
