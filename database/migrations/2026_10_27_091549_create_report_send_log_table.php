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
        // Отслеживает момент последней УСПЕШНОЙ отправки для каждого
        // периодического отчёта (report_key — произвольный слаг, см.
        // SendWeeklyDismissedReport). Окно отчёта считается "с last_sent_at
        // до сейчас" вместо фиксированного "прошлые N дней" — так пропущенный
        // по любой причине запуск (сервер лежал, деплой и т.п.) не теряет
        // данные: следующий запуск (авто или по кнопке) просто захватит весь
        // накопившийся период целиком.
        Schema::create('report_send_log', function (Blueprint $table) {
            $table->string('report_key')->primary();
            $table->dateTime('last_sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_send_log');
    }
};
