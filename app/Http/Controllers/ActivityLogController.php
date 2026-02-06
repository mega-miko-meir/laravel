<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use PhpOffice\PhpSpreadsheet\IOFactory;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// app/Http/Controllers/ActivityLogController.php
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityLogController extends Controller
{
    public function index()
    {
        $logs = ActivityLog::with('user')
            ->whereHas('user', function($query){
                $query->where('email', '!=', 'meirzhan.akimbekov@nobel.kz');
            })
            ->latest()
            ->paginate(20);

        return view('index', compact('logs'));
    }

    public function export(Request $request): StreamedResponse
    {
        $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date',
        ]);

        $from = $request->from . ' 00:00:00';
        $to   = $request->to . ' 23:59:59';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Заголовки
        $sheet->fromArray([
            'Пользователь',
            'URL',
            'Метод',
            'IP',
            'Дата',
        ], null, 'A1');

        // Немного стилей
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $sheet->freezePane('A2');

        $row = 2;

        ActivityLog::with('user')
            ->whereBetween('created_at', [$from, $to])
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

        // Автоширина колонок
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = "activity_logs_{$request->from}_{$request->to}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}

