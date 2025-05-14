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
        Schema::table('stages', function (Blueprint $table) {

            // 1. Zmieniamy nazwę kolumny name → stage (wymaga doctrine/dbal)
            $table->renameColumn('name', 'stage');

            // 2. Usuwamy kolumnę result
            $table->dropColumn('result');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            // 1. Przywracamy kolumnę result (jako string nullable)
            $table->string('result')->nullable();

            // 2. Zmieniamy nazwę stage → name
            $table->renameColumn('stage', 'name');
        });
    }
};
