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
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'crm_employee_id')) {
                $table->dropColumn('crm_employee_id');
            }
            if (Schema::hasColumn('employees', 'kmp_employee_name')) {
                $table->dropColumn('kmp_employee_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('crm_employee_id')->nullable()->after('status');
            $table->string('kmp_employee_name')->nullable()->after('crm_employee_id');
        });
    }
};
