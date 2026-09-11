<?php

namespace App\Http\Controllers;

use App\Services\TerritoryChangeService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TerritoryChangeController extends Controller
{
    public function index(Request $request, TerritoryChangeService $service)
    {
        $from = $request->input('date_from', now()->subMonth()->toDateString());
        $to   = $request->input('date_to', now()->toDateString());

        $changes = $service->getChanges($from, $to);

        return view('territory-changes', [
            'changes' => $changes,
            'from'    => $from,
            'to'      => $to,
        ]);
    }

    public function export(Request $request, TerritoryChangeService $service): BinaryFileResponse
    {
        $from = $request->input('date_from', now()->subMonth()->toDateString());
        $to   = $request->input('date_to', now()->toDateString());

        $changes = $service->getChanges($from, $to);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray([
            'ФИО', 'Дата смены', 'Старая группа', 'Новая группа',
            'Старая территория', 'Новая территория', 'Старый менеджер', 'Новый менеджер',
        ], null, 'A1');
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->freezePane('A2');

        $row = 2;
        foreach ($changes as $c) {
            $sheet->fromArray([
                $c->full_name,
                \Carbon\Carbon::parse($c->changed_at)->format('d.m.Y'),
                $c->old_team ?? '',
                $c->new_team ?? '',
                $c->old_territory ?? '',
                $c->new_territory ?? '',
                $c->old_manager ?? '',
                $c->new_manager ?? '',
            ], null, 'A' . $row);
            $row++;
        }

        foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'territory_changes_' . $from . '_' . $to . '.xlsx';
        $filePath = storage_path('app/' . $fileName);
        (new Xlsx($spreadsheet))->save($filePath);

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }
}
