<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropColumn('student_id');

            $table
                ->foreignId('user_id')
                ->nullable()
                ->constrained() 
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');

            $table
                ->foreignId('student_id')
                ->constrained()  
                ->onDelete('cascade');
        });
    }
};
