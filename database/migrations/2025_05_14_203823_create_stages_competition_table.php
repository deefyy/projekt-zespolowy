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
        Schema::create('stages_competition', function (Blueprint $table) {
            $table->id();
            // FK do tabeli competitions
            $table->foreignId('competition_id')
                  ->constrained()
                  ->cascadeOnDelete();
            // FK do tabeli stages
            $table->foreignId('stage_id')
                  ->constrained('stages')
                  ->cascadeOnDelete();
            // FK do tabeli students
            $table->foreignId('student_id')
                  ->constrained()
                  ->cascadeOnDelete();
            // Wynik – zachowujemy jako string, możesz zmienić na integer, gdy trzeba
            $table->string('result');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stages_competition');
    }
};
