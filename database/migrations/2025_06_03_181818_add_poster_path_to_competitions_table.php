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
        $table->string('poster_path')->nullable()->after('description');
    });
}

public function down(): void
{
    Schema::table('competitions', function (Blueprint $table) {
        $table->dropColumn('poster_path');
    });
}

};
