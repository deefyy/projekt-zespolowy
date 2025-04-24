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
        Schema::table('competitions', function (Blueprint $table) {
            // Sprawdzamy, czy kolumna student_id już istnieje
            if (Schema::hasColumn('competitions', 'student_id')) {
                // Jeśli kolumna istnieje, zmieniamy ją na nullable
                $table->foreignId('student_id')->nullable()->change();
            } else {
                // Dodajemy kolumnę, jeśli jeszcze jej nie ma
                $table->foreignId('student_id')->nullable()->constrained('students')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            // Sprawdzamy, czy kolumna student_id istnieje przed jej usunięciem
            if (Schema::hasColumn('competitions', 'student_id')) {
                $table->dropForeign(['student_id']);  // Usuwamy klucz obcy
                $table->dropColumn('student_id');     // Usuwamy kolumnę
            }
        });
    }
};
