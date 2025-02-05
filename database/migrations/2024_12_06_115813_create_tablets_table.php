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
        Schema::create('tablets', function (Blueprint $table) {
            $table->id();
            $table->string('model');
            $table->string('invent_number')->unique();
            $table->string('serial_number')->unique();
            $table->string('imei')->unique();
            $table->string('beeline_number')->unique();
            $table->string('beeline_number_status');
            $table->string('status');
            $table->string('old_employee_id');
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tablets');
    }
};
