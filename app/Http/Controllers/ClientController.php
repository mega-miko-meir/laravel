<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ClientController extends Controller
{

    public function index(Request $request)
    {
        $query = Client::query();

        // Поиск по ФИО
        if ($request->filled('full_name')) {
            $query->where('full_name', 'like', '%' . $request->full_name . '%');
        }

        // Специальность (multi-select)
        if ($request->filled('specialty')) {
            $query->whereIn('specialty', $request->specialty);
        }
        // dd(Client::pluck('brick_name'));

        // dd($request->brick_name);

        // Город (multi-select)
        if ($request->filled('city')) {
            $query->whereIn('city', $request->city);
        }

        if ($request->filled('brick_label')) {
            $query->whereIn('brick_label', $request->brick_label);
        }

        if ($request->filled('organization_type')) {
            $query->where('organization_type', $request->organization_type);
        }

        $clients = $query->paginate(50);

        // Данные для фильтров
        $specialties = Client::select('specialty')
        ->distinct()
        ->whereNotNull('specialty')
        ->where('specialty', '<>', '')
        ->orderBy('specialty', 'asc')
        ->pluck('specialty');
        $cities = Client::select('city')
        ->distinct()
        ->whereNotNull('city')
        ->where('city', '<>', '')
        ->orderBy('city', 'asc')
        ->pluck('city');
        $types = Client::select('organization_type')->distinct()->pluck('organization_type');
        $regions = Client::select('brick_label')
        ->distinct()
        ->where('brick_label', '<>', '')
        ->orderBy('brick_label', 'asc')
        ->pluck('brick_label');

        return view('clients', compact('clients', 'specialties', 'cities', 'types', 'regions'));
    }

    private function clientLabels($key)
    {
        return [
            'full_name' => 'ФИО/Название',
            'organization_type' => 'Тип клиента',
            'specialty' => 'Специальность',
            'specialty2' => 'Доп. специальность',
            'parent_organization' => 'Родительская организация',
            'workplace' => 'Место работы',
            'primary_address' => 'Адрес',
            'city' => 'Город',
            'brick_name' => 'Брик',
            'brick_label' => 'Регион',
            'onekey_id' => 'OneKey ID',
            'coordinates' => 'Координаты',
        ][$key] ?? $key;
    }

    public function export(Request $request)
    {
        // dd('export works');
        $query = Client::query();

        // фильтры
        if ($request->filled('full_name')) {
            $query->where('full_name', 'like', '%' . $request->full_name . '%');
        }

        if ($request->filled('specialty')) {
            $query->whereIn('specialty', $request->specialty);
        }

        if ($request->filled('city')) {
            $query->whereIn('city', $request->city);
        }

        if ($request->filled('organization_type')) {
            $query->where('organization_type', $request->organization_type);
        }

        $columns = $request->columns ?? ['full_name'];

        $data = $query->get($columns);

        // создаем Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // заголовки
        foreach ($columns as $index => $column) {
            $letter = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($letter . '1', $this->clientLabels($column));
        }


        // данные
        foreach ($data as $rowIndex => $row) {
            foreach ($columns as $colIndex => $column) {

                $letter = Coordinate::stringFromColumnIndex($colIndex + 1);
                $cell = $letter . ($rowIndex + 2);

                $sheet->setCellValue($cell, $row->$column);
            }
        }

        // имя файла
        $fileName = 'clients_export_' . now()->format('Y-m-d_H-i') . '.xlsx';

        // writer
        $writer = new Xlsx($spreadsheet);

        // отдаём файл в браузер
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

}
