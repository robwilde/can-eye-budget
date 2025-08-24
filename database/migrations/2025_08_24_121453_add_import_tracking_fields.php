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
            $table->string('original_description')->nullable()->after('description');
            $table->decimal('confidence_score', 3, 2)->nullable()->after('reconciled');
            $table->boolean('auto_categorized')->default(false)->after('confidence_score');
            $table->json('import_metadata')->nullable()->after('auto_categorized');
            
            $table->index(['auto_categorized']);
            $table->index(['confidence_score']);
        });
        
        Schema::table('imports', function (Blueprint $table) {
            $table->string('csv_file_path')->nullable()->after('filename');
            $table->json('column_mapping')->nullable()->after('csv_file_path');
            $table->json('processing_metadata')->nullable()->after('column_mapping');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['auto_categorized']);
            $table->dropIndex(['confidence_score']);
            
            $table->dropColumn([
                'original_description',
                'confidence_score',
                'auto_categorized',
                'import_metadata',
            ]);
        });
        
        Schema::table('imports', function (Blueprint $table) {
            $table->dropColumn([
                'csv_file_path',
                'column_mapping',
                'processing_metadata',
            ]);
        });
    }
};