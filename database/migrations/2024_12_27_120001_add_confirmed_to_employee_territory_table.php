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
        Schema::table('employee_territory', function (Blueprint $table) {
            $table->boolean('confirmed')->default(false); // Подтверждена ли запись
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_territory', function (Blueprint $table) {
            $table->dropColumn('confirmed');
        });
    }
};
