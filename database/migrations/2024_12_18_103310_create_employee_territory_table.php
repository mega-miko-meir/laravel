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
        Schema::create('employee_territory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('territory_id');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('unassigned_at')->nullable();
            $table->timestamps();

            // Связи с таблицами сотрудников и территорий
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('territory_id')->references('id')->on('territories')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_territory');
    }
};
