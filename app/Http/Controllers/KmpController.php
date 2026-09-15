<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Nobel\Kmp;
use App\Support\Etl;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class KmpController extends Controller
{
    private const CACHE_VERSION = 'v1';

    public function index(Request $request)
    {
        $year = (string) $request->input('year', now()->year);

        try {
            $data = $this->loadYear($year);
        } catch (\Exception $e) {
            return back()->withErrors(['nobel_db' => 'Не удалось получить данные из Nobel CRM. Попробуйте позже.']);
        }

        return view('kmp', [
            'year'        => $year,
            'initialData' => $data,
            'years'       => Cache::remember('kmp_filter_years', 3600, fn() => Kmp::distinct()->where('Статус заказа', 'Доставлено')->whereNotNull('Год')->orderBy('Год', 'desc')->pluck('Год')),
            'brands'      => Cache::remember('kmp_filter_brands', 3600, fn() => $this->distinctValues('Брэнд')),
            'cities'      => Cache::remember('kmp_filter_cities', 3600, fn() => $this->distinctValues('Город')),
            'depts'       => Cache::remember('kmp_filter_depts', 3600, fn() => $this->distinctValues('Бизнес-подразделение')),
            'empList'     => $this->empList(),
        ]);
    }

    /**
     * JSON-эндпоинт смены года без перезагрузки (тот же паттерн, что на
     * Визитах/Таргетных клиентах/...). Списки для фильтров (бренды, города,
     * подразделения, сотрудники, годы) не год-зависимы — грузятся один раз в
     * index() и повторно не запрашиваются.
     */
    public function data(Request $request)
    {
        $year = (string) $request->input('year', now()->year);

        try {
            return response()->json($this->loadYear($year), 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Не удалось получить данные из Nobel CRM. Попробуйте позже.']);
        }
    }

    /**
     * Строки года — дикт-кодированные (как на Визитах): повторяющиеся строковые
     * значения (МП, город, аптека, бренд, подразделение) выносятся в общий словарь,
     * а сами строки — массивы индексов. Вся вторичная фильтрация (МП/город/бренд/
     * подразделение), KPI-карточки, топ брендов/аптек считаются на клиенте из этого
     * одного набора — за год ~60 тыс. строк, укладывается в разумный payload (~2.5МБ).
     */
    private function loadYear(string $year): array
    {
        [$from, $to] = $this->yearBounds($year);

        return Cache::remember("kmp_year_" . self::CACHE_VERSION . "_{$year}", Etl::secondsUntilNextRun(), function () use ($from, $to) {
            return [
                'error' => null,
                'rows'  => $this->buildRows($from, $to),
                'trend' => $this->buildTrend($from, $to),
            ];
        });
    }

    private function buildRows(string $from, string $to): array
    {
        ini_set('memory_limit', '512M');

        $query = DB::connection('nobel')->table('kmp')
            ->where('Статус заказа', 'Доставлено')
            ->where('Дата', '>=', $from)
            ->where('Дата', '<=', $to)
            ->select([
                'Дата', 'Медпредставитель', 'Город', 'Название аптеки', 'ID аптеки',
                'Город аптеки', 'Брэнд', 'Бизнес-подразделение',
                'Amount_disc', 'Дост_колво',
            ]);

        $dict = [];
        $idx  = function (string $col, ?string $val) use (&$dict) {
            $val = $val ?? '';
            $dict[$col] ??= [];
            return $dict[$col][$val] ??= count($dict[$col]);
        };

        $rows = [];
        foreach ($query->orderBy('Дата')->cursor() as $r) {
            $rows[] = [
                $idx('date', $r->{'Дата'} ? substr($r->{'Дата'}, 0, 10) : null),
                $idx('employee', $r->{'Медпредставитель'}),
                $idx('city', $r->{'Город'}),
                $idx('pharmacy', $r->{'Название аптеки'}),
                $idx('pharmacyCity', $r->{'Город аптеки'}),
                $idx('brand', $r->{'Брэнд'}),
                $idx('dept', $r->{'Бизнес-подразделение'}),
                (float) $r->{'Amount_disc'},
                (float) $r->{'Дост_колво'},
                // отдельный ID аптеки — для точного подсчёта уникальных аптек
                // (одинаковые название+город бывают у разных сетевых точек)
                (string) $r->{'ID аптеки'},
            ];
        }

        $dictionaries = [];
        foreach ($dict as $col => $map) {
            $dictionaries[$col] = array_keys($map);
        }

        return [
            'cols'         => ['date', 'employee', 'city', 'pharmacy', 'pharmacyCity', 'brand', 'dept', 'amount', 'qty', 'pharmacyId'],
            'dictionaries' => $dictionaries,
            'data'         => $rows,
        ];
    }

    /** Обзорный тренд по месяцам выбранного года — не зависит от вторичных фильтров. */
    private function buildTrend(string $from, string $to): array
    {
        return DB::connection('nobel')->table('kmp')
            ->where('Статус заказа', 'Доставлено')
            ->where('Дата', '>=', $from)
            ->where('Дата', '<=', $to)
            ->selectRaw("DATE_FORMAT(`Дата`, '%Y-%m') as month, ROUND(SUM(`Amount_disc`)) as amount, ROUND(SUM(`Дост_колво`)) as qty, COUNT(*) as orders")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($r) => (array) $r)
            ->all();
    }

    private function empList(): array
    {
        // value — внутренний id сотрудника; names — все его варианты написания
        // в KMP (повторный найм) — на клиенте резолвим выбор в набор имён.
        return Employee::whereHas('kmpNames')
            ->with('kmpNames')
            ->orderBy('full_name')
            ->get(['id', 'full_name'])
            ->map(fn($e) => [
                'label' => $e->full_name,
                'value' => $e->id,
                'names' => $e->kmpNames->pluck('kmp_employee_name')->all(),
            ])
            ->values()
            ->all();
    }

    private const COLUMNS = [
        'Дата', 'Месяц', 'Год', 'Медпредставитель', 'Региональный менеджер',
        'Город', 'Название аптеки', 'ID аптеки', 'Город аптеки', 'Адрес аптеки',
        'БИН аптеки', 'Брэнд', 'Бизнес-подразделение', 'Номер заказа Pharmcenter',
        'SKU_splitted', 'Статус заказа', 'Цена_KZT', 'Размер_скидки', 'Заказ_упаковки',
        'Дост_скидка', 'Дост_цена', 'Дост_колво', 'Дост_сумма_скид',
        'Department', 'SW', 'Distributor', 'Distributor_branch',
        'Price', 'Amount', 'Discount_tot', 'Amount_disc', 'Amount_disc_tot',
    ];

    // Колонки с числами — заменяем '.' на ',' чтобы Excel с рус. локалью не читал их как даты
    private const NUMERIC_COLUMNS = [
        'Год', 'ID аптеки', 'Номер заказа Pharmcenter',
        'Цена_KZT', 'Размер_скидки', 'Заказ_упаковки',
        'Дост_скидка', 'Дост_цена', 'Дост_колво', 'Дост_сумма_скид',
        'Price', 'Amount', 'Discount_tot', 'Amount_disc', 'Amount_disc_tot',
    ];

    public function export(Request $request)
    {
        set_time_limit(0);

        $year = (string) $request->input('year', now()->year);
        [$defaultFrom, $defaultTo] = $this->yearBounds($year);
        $dateFrom = $request->input('date_from') ?: $defaultFrom;
        $dateTo   = $request->input('date_to') ?: $defaultTo;

        $q = $this->filtered($request, $dateFrom, $dateTo)->orderBy('Дата');

        $parts = array_filter([$dateFrom, $dateTo, $request->input('employee_id') ? 'emp' : null]);
        $fileName = 'kmp_' . (implode('_', $parts) ?: 'all') . '.csv';

        $numericSet = array_flip(self::NUMERIC_COLUMNS);

        return response()->streamDownload(function () use ($q, $numericSet) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, self::COLUMNS, ';');

            $q->chunk(500, function ($rows) use ($out, $numericSet) {
                foreach ($rows as $row) {
                    $attrs  = $row->getAttributes();
                    $values = array_map(function ($col) use ($attrs, $numericSet) {
                        $val = $attrs[$col] ?? '';
                        if (isset($numericSet[$col]) && $val !== '' && $val !== null) {
                            return str_replace('.', ',', $val);
                        }
                        return $val;
                    }, self::COLUMNS);
                    fputcsv($out, $values, ';');
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

    private function filtered(Request $request, string $dateFrom, string $dateTo)
    {
        $q = Kmp::query()
            ->where('Статус заказа', 'Доставлено')
            ->where('Дата', '>=', $dateFrom)
            ->where('Дата', '<=', $dateTo);
        if ($request->filled('employee_id')) {
            $kmpNames = Employee::find($request->input('employee_id'))?->kmp_employee_names ?? [];
            $q->whereIn('Медпредставитель', $kmpNames ?: ['__none__']);
        }
        if ($request->filled('city'))  $q->whereIn('Город', (array) $request->input('city'));
        if ($request->filled('brand')) $q->whereIn('Брэнд', (array) $request->input('brand'));
        if ($request->filled('dept'))  $q->whereIn('Бизнес-подразделение', (array) $request->input('dept'));
        return $q;
    }

    private function distinctValues(string $col): \Illuminate\Support\Collection
    {
        return Kmp::distinct()
            ->where('Статус заказа', 'Доставлено')
            ->whereNotNull($col)->where($col, '<>', '')
            ->orderBy($col)
            ->pluck($col);
    }

    /** @return array{0: string, 1: string} [yearStart, yearEnd] в формате Y-m-d */
    private function yearBounds(string $year): array
    {
        $date = Carbon::createFromDate((int) $year, 1, 1)->startOfYear();
        return [$date->toDateString(), $date->copy()->endOfYear()->toDateString()];
    }
}
