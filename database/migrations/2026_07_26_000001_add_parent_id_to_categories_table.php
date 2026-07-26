<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('categories')
                ->cascadeOnDelete();

            $table->index(['parent_id', 'position']);
        });
    }

    public function down(): void
    {
        // SQLite refuses to drop a column that is still part of a foreign key
        // or an index, so the constraint has to go first in its own rebuild.
        Schema::table('categories', function (Blueprint $table): void {
            $table->dropForeign(['parent_id']);
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropIndex(['parent_id', 'position']);
            $table->dropColumn('parent_id');
        });
    }
};
