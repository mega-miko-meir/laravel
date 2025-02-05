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
        Schema::table('territories', function (Blueprint $table) {
            // Adding unique constraint to the existing employee_id column
            $table->unique('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('territories', function (Blueprint $table) {
            // Dropping the unique constraint
            $table->dropUnique(['employee_id']);
        });
    }
};
