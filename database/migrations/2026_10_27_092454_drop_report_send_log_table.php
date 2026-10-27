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
        // Отчёт об увольнениях/декрете вернули на фиксированное окно
        // "последние 2 недели" вместо отслеживания "с прошлой отправки" —
        // таблица больше не нужна (см. create_report_send_log_table).
        Schema::dropIfExists('report_send_log');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('report_send_log', function (Blueprint $table) {
            $table->string('report_key')->primary();
            $table->dateTime('last_sent_at')->nullable();
            $table->timestamps();
        });
    }
};
