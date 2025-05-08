<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('competitions', function (Blueprint $table) {
            // 1. Usuń starą relację do studentów
            $table->dropForeign(['student_id']);
            $table->dropColumn('student_id');

            // 2. Dodaj nową kolumnę user_id, pozwól na null i ustaw on delete set null
            $table
                ->foreignId('user_id')
                ->nullable()
                ->constrained()        // domyślnie do users.id
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('competitions', function (Blueprint $table) {
            // 1. Usuń relację do users
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');

            // 2. Przywróć kolumnę student_id + FK do students
            $table
                ->foreignId('student_id')
                ->constrained()        // do students.id
                ->onDelete('cascade'); // albo inna logika, jak chcesz
        });
    }
};
