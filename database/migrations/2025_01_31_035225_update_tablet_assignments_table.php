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
        // Переименовываем таблицу
        // Schema::rename('tablet_assignments', 'employee_tablet');

        Schema::table('employee_tablet', function(Blueprint $table){
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('tablet_id');
            // $table->timestamp('assigned_at')->nullable();
            // $table->timestamp('returned_at')->nullable();
            // $table->boolean('confirmed')->default(false); // Подтверждена ли запись
            $table->string('pdf_path')->nullable()->after('tablet_id'); // Добавляем pdf_path
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Переименовываем обратно
        // Schema::rename('employee_tablet', 'tablet_assignments');

        // 2. Убираем новые колонки
        Schema::table('tablet_assignments', function (Blueprint $table) {
            $table->dropColumn(['pdf_path', 'employee_id', 'tablet_id']);
        });
    }
};
