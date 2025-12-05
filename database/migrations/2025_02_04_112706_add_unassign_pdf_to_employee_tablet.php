<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(){
        Schema::table('employee_tablet', function (Blueprint $table) {
            $table->string('unassign_pdf')->nullable()->after('pdf_path');
        });
    }

    public function down()
    {
        Schema::table('employee_tablet', function (Blueprint $table) {
            $table->dropColumn(['unassign_pdf']);
        });
    }
};
