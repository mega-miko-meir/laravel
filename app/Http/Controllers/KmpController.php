<?php

namespace App\Http\Controllers;

use App\Models\Nobel\Kmp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class KmpController extends Controller
{
    public function index(Request $request)
    {
        try {
            $allowedSorts = ['Дата', 'Медпредставитель', 'Название аптеки', 'Брэнд', 'Amount_disc', 'Дост_колво'];
            $sortCol = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'Дата';
            $sortDir = $request->input('dir') === 'asc' ? 'asc' : 'desc';

            // Кеш агрегатов по набору фильтров (кроме сортировки и пагинации)
            $filterParams = $request->only(['year', 'date_from', 'date_to', 'employee', 'kmp_employee_name', 'city', 'brand', 'dept']);
            if (empty($filterParams['year'])) $filterParams['year'] = '2026';
            $aggCacheKey  = 'kmp_agg_' . md5(json_encode($filterParams));

            $agg = Cache::remember($aggCacheKey, 1800, function () use ($request) {
                $kpi = $this->filtered($request)
                    ->selectRaw('
                        COUNT(*) as total_orders,
                        ROUND(SUM(`Amount_disc`)) as total_amount,
                        ROUND(SUM(`Дост_колво`)) as total_qty,
                        COUNT(DISTINCT `Медпредставитель`) as emp_count,
                        COUNT(DISTINCT `ID аптеки`) as pharmacy_count,
                        COUNT(DISTINCT `Брэнд`) as brand_count
                    ')
                    ->first();

                $monthlyTrend = $this->filtered($request)
                    ->selectRaw("DATE_FORMAT(`Дата`, '%Y-%m') as month, ROUND(SUM(`Amount_disc`)) as amount, ROUND(SUM(`Дост_колво`)) as qty, COUNT(*) as orders")
                    ->whereNotNull('Дата')
                    ->groupBy('month')
                    ->orderBy('month')
                    ->limit(24)
                    ->get();

                $topBrands = $this->filtered($request)
                    ->selectRaw('`Брэнд` as brand, ROUND(SUM(`Amount_disc`)) as amount, ROUND(SUM(`Дост_колво`)) as qty, COUNT(*) as orders')
                    ->whereNotNull('Брэнд')->where('Брэнд', '<>', '')
                    ->groupBy('Брэнд')
                    ->orderByDesc('amount')
                    ->limit(15)
                    ->get();

                $topBrandsByQty = $this->filtered($request)
                    ->selectRaw('`Брэнд` as brand, ROUND(SUM(`Amount_disc`)) as amount, ROUND(SUM(`Дост_колво`)) as qty, COUNT(*) as orders')
                    ->whereNotNull('Брэнд')->where('Брэнд', '<>', '')
                    ->groupBy('Брэнд')
                    ->orderByDesc('qty')
                    ->limit(15)
                    ->get();

                $topPharmacies = $this->filtered($request)
                    ->selectRaw('`Название аптеки` as name, `Город аптеки` as city, ROUND(SUM(`Amount_disc`)) as amount, ROUND(SUM(`Дост_колво`)) as qty, COUNT(*) as orders')
                    ->whereNotNull('Название аптеки')->where('Название аптеки', '<>', '')
                    ->groupBy('Название аптеки', 'Город аптеки')
                    ->orderByDesc('amount')
                    ->limit(10)
                    ->get();

                return compact('kpi', 'monthlyTrend', 'topBrands', 'topBrandsByQty', 'topPharmacies');
            });

            ['kpi' => $kpi, 'monthlyTrend' => $monthlyTrend, 'topBrands' => $topBrands, 'topBrandsByQty' => $topBrandsByQty, 'topPharmacies' => $topPharmacies] = $agg;

            // Таблица — не кешируется (зависит от сортировки и страницы)
            $rows = $this->filtered($request)->orderBy($sortCol, $sortDir)->paginate(25);

            $brands = Cache::remember('kmp_filter_brands', 3600, fn() => $this->distinctValues('Брэнд'));
            $cities = Cache::remember('kmp_filter_cities', 3600, fn() => $this->distinctValues('Город'));
            $years  = Cache::remember('kmp_filter_years',  3600, fn() => Kmp::distinct()->where('Статус заказа', 'Доставлено')->whereNotNull('Год')->orderBy('Год', 'desc')->pluck('Год'));
            $depts  = Cache::remember('kmp_filter_depts',  3600, fn() => $this->distinctValues('Бизнес-подразделение'));

            $empList = \App\Models\Employee::whereNotNull('kmp_employee_name')
                ->orderBy('full_name')
                ->get(['full_name', 'kmp_employee_name'])
                ->map(fn($e) => ['label' => $e->full_name, 'value' => $e->kmp_employee_name])
                ->values();

        } catch (\Exception $e) {
            return back()->withErrors(['nobel_db' => 'Nobel DB недоступна: ' . $e->getMessage()]);
        }

        return view('kmp', compact(
            'rows', 'brands', 'cities', 'years', 'depts', 'empList',
            'kpi', 'monthlyTrend', 'topBrands', 'topBrandsByQty', 'topPharmacies',
            'sortCol', 'sortDir'
        ));
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

        $q = $this->filtered($request)->orderBy('Дата');

        $parts = array_filter([
            $request->input('year'),
            $request->input('kmp_employee_name') ? 'emp' : null,
            $request->input('date_from'),
            $request->input('date_to'),
        ]);
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

    private function filtered(Request $request)
    {
        $q = Kmp::query()->where('Статус заказа', 'Доставлено');
        $year = $request->input('year', '2026');
        if ($year !== '')                          $q->where('Год', $year);
        if ($request->filled('date_from'))         $q->where('Дата', '>=', $request->input('date_from'));
        if ($request->filled('date_to'))           $q->where('Дата', '<=', $request->input('date_to'));
        if ($request->filled('employee'))          $q->where('Медпредставитель', 'like', '%' . $request->input('employee') . '%');
        if ($request->filled('kmp_employee_name')) $q->where('Медпредставитель', $request->input('kmp_employee_name'));
        if ($request->filled('city'))              $q->whereIn('Город', (array) $request->input('city'));
        if ($request->filled('brand'))             $q->whereIn('Брэнд', (array) $request->input('brand'));
        if ($request->filled('dept'))              $q->whereIn('Бизнес-подразделение', (array) $request->input('dept'));
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
}
