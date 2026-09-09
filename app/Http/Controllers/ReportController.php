<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

class ReportController extends Controller
{
    /**
     * Ручной принудительный запуск еженедельного отчёта об увольнениях —
     * та же команда и то же окно (прошлая пн-вс), что и в расписании по
     * понедельникам в 08:00 (routes/console.php). Нужен, чтобы не ждать
     * следующего понедельника при проверке/повторной отправке.
     */
    public function sendWeeklyDismissed()
    {
        Artisan::call('report:weekly-dismissed');
        $output = trim(Artisan::output());

        return back()->with('success', $output ?: 'Отчёт отправлен.');
    }
}
