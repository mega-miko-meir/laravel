<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientExportRequest;
use App\Http\Requests\ClientIndexRequest;
use App\Models\Nobel\OnekeyDoctor;
use App\Models\Nobel\OnekeyPharmacy;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    private const DOCTOR_COLUMNS = [
        'customer'            => 'ФИО',
        'customer_spesiality' => 'Специальность',
        'organization'        => 'Место работы',
        'organization_address'=> 'Адрес',
        'town'                => 'Город',
        'province'            => 'Регион',
    ];

    private const PHARMACY_COLUMNS = [
        'organization'        => 'Название',
        'organization_address'=> 'Адрес',
        'town'                => 'Город',
        'province'            => 'Регион',
    ];

    public function index(ClientIndexRequest $request)
    {
        $isPharmacy = $request->input('organization_type') === 'Аптека';

        $query = $isPharmacy ? OnekeyPharmacy::query() : OnekeyDoctor::query();
        $this->applyFilters($query, $request, $isPharmacy);
        $clients = $query->paginate(50);

        $specialties = $isPharmacy
            ? collect()
            : OnekeyDoctor::distinct()
                ->whereNotNull('customer_spesiality')
                ->where('customer_spesiality', '<>', '')
                ->orderBy('customer_spesiality')
                ->pluck('customer_spesiality');

        $model = $isPharmacy ? new OnekeyPharmacy : new OnekeyDoctor;

        $cities = $model::distinct()
            ->whereNotNull('town')->where('town', '<>', '')
            ->orderBy('town')->pluck('town');

        $regions = $model::distinct()
            ->whereNotNull('province')->where('province', '<>', '')
            ->orderBy('province')->pluck('province');

        return view('clients', compact(
            'clients', 'specialties', 'cities', 'regions', 'isPharmacy'
        ));
    }

    public function export(ClientExportRequest $request)
    {
        $isPharmacy = $request->input('organization_type') === 'Аптека';
        $available  = $isPharmacy ? self::PHARMACY_COLUMNS : self::DOCTOR_COLUMNS;

        $query = $isPharmacy ? OnekeyPharmacy::query() : OnekeyDoctor::query();
        $this->applyFilters($query, $request, $isPharmacy);

        $requestedCols = $request->input('columns', []);
        $columns = array_values(array_intersect(array_keys($available), $requestedCols))
            ?: array_keys($available);
        $labels  = array_map(fn($col) => $available[$col], $columns);

        $fileName = 'onekey_' . ($isPharmacy ? 'pharmacy' : 'doctors') . '_'
            . now()->format('Y-m-d_H-i') . '.csv';

        return response()->streamDownload(function () use ($query, $columns, $labels) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, $labels, ';');

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

    private function applyFilters($query, Request $request, bool $isPharmacy): void
    {
        $nameCol = $isPharmacy ? 'organization' : 'customer';

        if ($request->filled('full_name')) {
            $query->where($nameCol, 'like', '%' . $request->input('full_name') . '%');
        }

        if (!$isPharmacy && $request->filled('specialty')) {
            $query->whereIn('customer_spesiality', (array) $request->input('specialty'));
        }

        if ($request->filled('city')) {
            $query->whereIn('town', (array) $request->input('city'));
        }

        if ($request->filled('brick_label')) {
            $query->whereIn('province', (array) $request->input('brick_label'));
        }
    }
}
