<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('teacher')->nullable();
            $table->string('guardian')->nullable();
            $table->string('contact');
            $table->boolean('statement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            //$table->dropColumn('teacher/guardian');
            //$table->dropColumn('contact');
            //$table->dropColumn('statement');
        });
    }
};
