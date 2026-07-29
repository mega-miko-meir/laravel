<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientExportRequest;
use App\Http\Requests\ClientIndexRequest;
use App\Models\Nobel\OnekeyDoctor;
use App\Models\Nobel\OnekeyPharmacy;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    private const DOCTOR_COLUMNS = [
        'customer_id'         => 'OneKey ID',
        'customer'            => 'ФИО',
        'customer_spesiality' => 'Специальность',
        'organization'        => 'Место работы',
        'organization_address'=> 'Адрес',
        'town'                => 'Город',
        'province'            => 'Регион',
    ];

    private const PHARMACY_COLUMNS = [
        'organization_id'     => 'OneKey ID',
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
        $idCol = $isPharmacy ? 'organization_id' : 'customer_id';

        // COUNT(DISTINCT id) на некэшируемом filtered-запросе — дёшево (индекс по id).
        // Обычный paginate() тут в разы дороже: Laravel оборачивает GROUP BY + все
        // MAX(...)-колонки в подзапрос ради подсчёта total, пересчитывая все агрегаты.
        $total = (clone $query)->selectRaw("COUNT(DISTINCT `$idCol`) as cnt")->value('cnt');

        $this->groupByUnique($query, array_keys($isPharmacy ? self::PHARMACY_COLUMNS : self::DOCTOR_COLUMNS), $idCol);
        $perPage = 50;
        $page    = LengthAwarePaginator::resolveCurrentPage();
        $items   = $query->forPage($page, $perPage)->get();

        $clients = new LengthAwarePaginator($items, $total, $perPage, $page, [
            'path'  => $request->url(),
            'query' => $request->query(),
        ]);

        // DISTINCT+ORDER BY по TEXT-колонкам без индекса — дорого (temp table на диске),
        // а список специальностей/городов/регионов меняется редко. Кэшируем на 1 час,
        // как и фильтры /calls (см. CallController).
        $specialties = $isPharmacy
            ? collect()
            : Cache::remember('clients_filter_specialties', 3600, fn() => OnekeyDoctor::distinct()
                ->whereNotNull('customer_spesiality')
                ->where('customer_spesiality', '<>', '')
                ->orderBy('customer_spesiality')
                ->pluck('customer_spesiality'));

        $model     = $isPharmacy ? OnekeyPharmacy::class : OnekeyDoctor::class;
        $cacheType = $isPharmacy ? 'pharmacy' : 'doctors';

        $cities = Cache::remember("clients_filter_towns_$cacheType", 3600, fn() => $model::distinct()
            ->whereNotNull('town')->where('town', '<>', '')
            ->orderBy('town')->pluck('town'));

        $regions = Cache::remember("clients_filter_provinces_$cacheType", 3600, fn() => $model::distinct()
            ->whereNotNull('province')->where('province', '<>', '')
            ->orderBy('province')->pluck('province'));

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

        $idCol = $isPharmacy ? 'organization_id' : 'customer_id';
        $this->groupByUnique($query, array_unique([...$columns, $idCol]), $idCol);

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

    /**
     * OneKey-таблицы содержат по несколько идентичных строк на одну карточку
     * (история добавлений в "мой список" у разных представителей). Группируем
     * по ID, чтобы вернуть ровно одну строку на врача/аптеку.
     */
    private function groupByUnique($query, array $columns, string $idCol): void
    {
        $selects = array_map(
            fn($col) => $col === $idCol ? $idCol : DB::raw("MAX(`$col`) as `$col`"),
            $columns
        );

        $query->select($selects)->groupBy($idCol);
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
