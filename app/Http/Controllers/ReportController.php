<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

class ReportController extends Controller
{
    /**
     * Ручной принудительный запуск отчёта об увольнениях и декретах — та же
     * команда, что и в расписании раз в 2 недели (routes/console.php). Окно
     * отчёта — "с прошлой успешной отправки до сейчас" (см.
     * SendWeeklyDismissedReport), так что нажатие кнопки не создаёт пропусков
     * или дублей в следующей авто-рассылке.
     */
    public function sendWeeklyDismissed()
    {
        Artisan::call('report:weekly-dismissed');
        $output = trim(Artisan::output());

        return back()->with('success', $output ?: 'Отчёт отправлен.');
    }
}
