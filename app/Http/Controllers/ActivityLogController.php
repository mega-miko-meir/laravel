<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActivityLogExportRequest;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityLogController extends Controller
{
    private const METHODS = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

    public function index(Request $request)
    {
        // "Скрыть мои действия" включён по умолчанию (query-параметр отсутствует
        // только при первом заходе) — это сохраняет прежнее поведение (раньше
        // свои действия были жёстко скрыты хардкодом), но теперь это просто
        // дефолт чекбокса, а не непреодолимое условие в запросе.
        $hideOwn = $request->input('hide_own', '1') === '1';
        $userId  = $request->input('user_id');
        $methods = array_values(array_intersect((array) $request->input('method', []), self::METHODS));
        $search  = trim((string) $request->input('q', ''));
        $from    = $request->input('from');
        $to      = $request->input('to');

        $logs = ActivityLog::with('user')
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when(!$userId && $hideOwn, fn ($q) => $q->where('user_id', '!=', auth()->id()))
            ->when($methods, fn ($q) => $q->whereIn('method', $methods))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('url', 'like', "%{$search}%")
                        ->orWhere('ip', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('full_name', 'like', "%{$search}%"));
                });
            })
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from . ' 00:00:00'))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to . ' 23:59:59'))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        // Живой фильтр (та же схема, что на «Сотрудниках»): при AJAX-запросе
        // отдаём только перерисованную таблицу, без полного рендера страницы.
        if ($request->ajax()) {
            return response(
                view('components.activity-log-table', compact('logs'))->render()
            )->header('X-Total-Count', $logs->total());
        }

        // Только пользователи, у которых реально есть записи — не весь справочник employees.
        $users = User::whereIn('id', ActivityLog::select('user_id')->distinct())
            ->orderBy('full_name')
            ->get(['id', 'full_name']);

        return view('index', [
            'logs'    => $logs,
            'users'   => $users,
            'methods' => self::METHODS,
            'filters' => compact('hideOwn', 'userId', 'methods', 'search', 'from', 'to'),
        ]);
    }

    public function export(ActivityLogExportRequest $request): StreamedResponse
    {
        $validated = $request->validated();

        $from = $validated['from'] . ' 00:00:00';
        $to   = $validated['to'] . ' 23:59:59';

        // Выгрузка отражает те же фильтры, что сейчас применены на экране
        // (панель экспорта подставляет их скрытыми полями при открытии) —
        // тот же принцип, что и на странице «Сотрудники».
        $userId  = $request->input('user_id');
        $hideOwn = $request->input('hide_own') === '1';
        $methods = array_values(array_intersect((array) $request->input('method', []), self::METHODS));
        $search  = trim((string) $request->input('q', ''));

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray([
            'Пользователь',
            'URL',
            'Метод',
            'IP',
            'Дата',
        ], null, 'A1');

        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $sheet->freezePane('A2');

        $row = 2;

        ActivityLog::with('user')
            ->whereBetween('created_at', [$from, $to])
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when(!$userId && $hideOwn, fn ($q) => $q->where('user_id', '!=', auth()->id()))
            ->when($methods, fn ($q) => $q->whereIn('method', $methods))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('url', 'like', "%{$search}%")
                        ->orWhere('ip', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('full_name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('created_at')
            ->chunk(500, function ($logs) use (&$row, $sheet) {
                foreach ($logs as $log) {
                    $sheet->fromArray([
                        $log->user?->full_name ?? 'Гость',
                        $log->url,
                        $log->method,
                        $log->ip,
                        $log->created_at->format('d.m.Y H:i'),
                    ], null, 'A' . $row);

                    $row++;
                }
            });

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = "activity_logs_{$validated['from']}_{$validated['to']}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
