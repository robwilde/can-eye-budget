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
        Schema::create('category_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->enum('field', ['description', 'amount']);
            $table->enum('operator', ['contains', 'equals', 'greater_than', 'less_than', 'starts_with', 'ends_with']);
            $table->string('value');
            $table->unsignedInteger('priority')->default(1);
            $table->timestamps();

            $table->index(['category_id', 'priority']);
            $table->index('field');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_rules');
    }
};
