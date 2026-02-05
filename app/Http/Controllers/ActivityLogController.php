<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;

// app/Http/Controllers/ActivityLogController.php
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityLogController extends Controller
{
    public function index()
    {
        $logs = ActivityLog::with('user')
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

        $fileName = "activity_logs_{$request->from}_{$request->to}.csv";

        return response()->streamDownload(function () use ($from, $to) {

            $handle = fopen('php://output', 'w');

            // заголовки CSV
            fputcsv($handle, [
                'Пользователь',
                'URL',
                'Метод',
                'IP',
                'Дата'
            ]);

            ActivityLog::with('user')
                ->whereBetween('created_at', [$from, $to])
                ->orderByDesc('created_at')
                ->chunk(500, function ($logs) use ($handle) {
                    foreach ($logs as $log) {
                        fputcsv($handle, [
                            $log->user?->full_name ?? 'Гость',
                            $log->url,
                            $log->method,
                            $log->ip,
                            $log->created_at->format('d.m.Y H:i'),
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

