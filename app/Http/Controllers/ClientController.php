<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientExportRequest;
use App\Http\Requests\ClientIndexRequest;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    private const EXPORTABLE_COLUMNS = [
        'full_name',
        'organization_type',
        'specialty',
        'specialty2',
        'parent_organization',
        'workplace',
        'primary_address',
        'city',
        'brick_name',
        'brick_label',
        'onekey_id',
        'coordinates',
    ];

    public function index(ClientIndexRequest $request)
    {
        $query = Client::query();
        $this->applyFilters($query, $request);

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

    public function export(ClientExportRequest $request)
    {
        $query = Client::query();
        $this->applyFilters($query, $request);
        $columns = $this->sanitizeColumns($request->input('columns', ['full_name']));
        $labels  = array_map(fn($col) => $this->clientLabels($col), $columns);
        $fileName = 'clients_export_' . now()->format('Y-m-d_H-i') . '.csv';

        return response()->streamDownload(function () use ($query, $columns, $labels) {
            $out = fopen('php://output', 'w');

            // BOM для корректного открытия в Excel
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, $labels, ';');

            // чанки по 500 строк — память не растёт с размером таблицы
            $query->chunk(500, function ($rows) use ($out, $columns) {
                foreach ($rows as $row) {
                    fputcsv($out, array_map(fn($col) => $row->$col ?? '', $columns), ';');
                }
                ob_flush();
                flush();
            });

            fclose($out);
        }, $fileName, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('full_name')) {
            $query->where('full_name', 'like', '%' . $request->input('full_name') . '%');
        }

        if ($request->filled('specialty')) {
            $query->whereIn('specialty', (array) $request->input('specialty'));
        }

        if ($request->filled('city')) {
            $query->whereIn('city', (array) $request->input('city'));
        }

        if ($request->filled('brick_label')) {
            $query->whereIn('brick_label', (array) $request->input('brick_label'));
        }

        if ($request->filled('organization_type')) {
            $query->where('organization_type', $request->input('organization_type'));
        }
    }

    private function sanitizeColumns(mixed $columns): array
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $columns = array_values(array_intersect($columns, self::EXPORTABLE_COLUMNS));

        return $columns !== [] ? $columns : ['full_name'];
    }

}
