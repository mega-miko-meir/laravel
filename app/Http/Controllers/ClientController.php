<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientExportRequest;
use App\Http\Requests\ClientIndexRequest;
use App\Models\Nobel\OnekeyDoctor;
use App\Models\Nobel\OnekeyPharmacy;
use App\Support\Etl;
use Illuminate\Http\Request;
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

    private const CACHE_VERSION = 'v1';

    public function index(ClientIndexRequest $request)
    {
        $type = $request->input('organization_type') === 'Аптека' ? 'Аптека' : 'Специалист';

        try {
            $initialData = $this->loadType($type);
        } catch (\Exception $e) {
            // Nobel DB недоступна — показываем страницу без данных вместо падения
            $initialData = $this->emptyPayload($type, 'Не удалось получить данные из Nobel CRM. Попробуйте позже.');
        }

        return view('clients', [
            'type'        => $type,
            'initialData' => $initialData,
        ]);
    }

    /**
     * JSON-эндпоинт для переключения «Специалист/Аптека» без перезагрузки страницы —
     * тот же паттерн, что на Визитах/КМП. Фильтры (регион/город/специальность/ФИО) и
     * пагинация работают на клиенте по одному загруженному набору.
     */
    public function data(Request $request)
    {
        $type = $request->input('organization_type') === 'Аптека' ? 'Аптека' : 'Специалист';

        try {
            return response()->json($this->loadType($type), 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json($this->emptyPayload($type, 'Не удалось получить данные из Nobel CRM. Попробуйте позже.'));
        }
    }

    /**
     * В OneKey всего ~60 тыс. врачей и ~10 тыс. аптек (после дедупликации) — это
     * помещается в один дикт-кодированный payload, поэтому вместо серверной фильтрации
     * с перезагрузкой страницы отдаём всё разом. Справочники регионов/городов/
     * специальностей берутся из самих словарей (отдельные DISTINCT-запросы не нужны).
     * Справочная база обновляется ночным ETL — кэшируем до его следующего запуска.
     */
    private function loadType(string $type): array
    {
        $isPharmacy = $type === 'Аптека';
        $key = 'onekey_' . ($isPharmacy ? 'pharmacy' : 'doctors') . '_' . self::CACHE_VERSION;

        return Cache::remember($key, Etl::secondsUntilNextRun(), function () use ($isPharmacy, $type) {
            ini_set('memory_limit', '512M');

            $dict = [];
            $idx = function (string $col, ?string $val) use (&$dict) {
                $val = $val ?? '';
                $dict[$col] ??= [];
                return $dict[$col][$val] ??= count($dict[$col]);
            };

            $rows = [];
            if ($isPharmacy) {
                $cols  = ['id', 'name', 'address', 'province', 'town'];
                $query = DB::connection('nobel')->table((new OnekeyPharmacy)->getTable())
                    ->select([
                        'organization_id',
                        DB::raw('MAX(`organization`) as organization'),
                        DB::raw('MAX(`organization_address`) as organization_address'),
                        DB::raw('MAX(`province`) as province'),
                        DB::raw('MAX(`town`) as town'),
                    ])->groupBy('organization_id');

                foreach ($query->cursor() as $r) {
                    $rows[] = [$r->organization_id, $r->organization, $r->organization_address,
                        $idx('province', $r->province), $idx('town', $r->town)];
                }
            } else {
                $cols  = ['id', 'name', 'specialty', 'organization', 'province', 'town'];
                $query = DB::connection('nobel')->table((new OnekeyDoctor)->getTable())
                    ->select([
                        'customer_id',
                        DB::raw('MAX(`customer`) as customer'),
                        DB::raw('MAX(`customer_spesiality`) as customer_spesiality'),
                        DB::raw('MAX(`organization`) as organization'),
                        DB::raw('MAX(`province`) as province'),
                        DB::raw('MAX(`town`) as town'),
                    ])->groupBy('customer_id');

                foreach ($query->cursor() as $r) {
                    $rows[] = [$r->customer_id, $r->customer,
                        $idx('specialty', $r->customer_spesiality), $idx('organization', $r->organization),
                        $idx('province', $r->province), $idx('town', $r->town)];
                }
            }

            $dictionaries = [];
            foreach ($dict as $col => $map) {
                $dictionaries[$col] = array_keys($map);
            }

            return ['error' => null, 'type' => $type, 'cols' => $cols, 'dictionaries' => $dictionaries, 'data' => $rows];
        });
    }

    private function emptyPayload(string $type, string $error): array
    {
        $cols = $type === 'Аптека'
            ? ['id', 'name', 'address', 'province', 'town']
            : ['id', 'name', 'specialty', 'organization', 'province', 'town'];

        return ['error' => $error, 'type' => $type, 'cols' => $cols, 'dictionaries' => new \stdClass, 'data' => []];
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
