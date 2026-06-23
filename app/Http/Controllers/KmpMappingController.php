<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class KmpMappingController extends Controller
{
    // Distinct МП names from kmp view
    private function getKmpEmployees(): array
    {
        return Cache::remember('kmp_employees_list', 3600, function () {
            try {
                return DB::connection('nobel')
                    ->select('SELECT TRIM(`Медпредставитель`) as name
                              FROM kmp
                              WHERE `Статус заказа` = "Доставлено"
                                AND `Медпредставитель` IS NOT NULL AND `Медпредставитель` <> ""
                              GROUP BY TRIM(`Медпредставитель`)
                              ORDER BY `Медпредставитель`');
            } catch (\Exception $e) {
                return [];
            }
        });
    }

    public function index()
    {
        $kmpEmployees = $this->getKmpEmployees();

        $sysEmployees = Employee::orderBy('full_name')
            ->get(['id', 'full_name', 'position', 'kmp_employee_name']);

        $linkedByName = $sysEmployees->whereNotNull('kmp_employee_name')
            ->keyBy('kmp_employee_name');

        $kmpTotal = count($kmpEmployees);
        $mapped   = $linkedByName->count();

        return view('admin.kmp-mapping', compact(
            'kmpEmployees', 'sysEmployees', 'linkedByName', 'kmpTotal', 'mapped'
        ));
    }

    public function link(Request $request)
    {
        try {
            $request->validate([
                'kmp_name'    => 'required|string',
                'employee_id' => 'nullable|integer|exists:employees,id',
            ]);

            $kmpName    = trim($request->input('kmp_name'));
            $employeeId = $request->input('employee_id');

            Employee::where('kmp_employee_name', $kmpName)->update(['kmp_employee_name' => null]);

            if ($employeeId !== null && $employeeId !== '') {
                $emp = Employee::findOrFail((int) $employeeId);
                $emp->kmp_employee_name = $kmpName;
                $emp->save();
                return back()->with('success', "KMP-сотрудник привязан к «{$emp->full_name}».");
            }

            return back()->with('success', 'Привязка сброшена.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка: ' . $e->getMessage());
        }
    }

    public function autoMatch()
    {
        set_time_limit(0);

        try {
            $kmpEmployees = $this->getKmpEmployees();

            if (empty($kmpEmployees)) {
                return back()->with('error', 'Список KMP-сотрудников пуст или Nobel DB недоступна.');
            }

            // Lookup: первые два слова KMP-имени => полное имя
            $kmpByShName = [];
            foreach ($kmpEmployees as $r) {
                $parts = preg_split('/\s+/', trim($r->name));
                $sh = implode(' ', array_slice($parts, 0, 2));
                if (!isset($kmpByShName[$sh])) {
                    $kmpByShName[$sh] = $r->name;
                }
            }

            $alreadyLinked = Employee::whereNotNull('kmp_employee_name')
                ->pluck('kmp_employee_name')
                ->flip();

            $matched = 0;
            foreach (Employee::all() as $emp) {
                if ($emp->kmp_employee_name) continue;
                $shName = $emp->sh_name;
                if (!$shName) continue;

                if (isset($kmpByShName[$shName])) {
                    $kmpName = $kmpByShName[$shName];
                    if ($alreadyLinked->has($kmpName)) continue;
                    $emp->kmp_employee_name = $kmpName;
                    $emp->save();
                    $alreadyLinked->put($kmpName, true);
                    $matched++;
                }
            }

            Cache::forget('kmp_employees_list');

            return back()->with('success', "Автоматически привязано: {$matched} сотрудников.");

        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка автопривязки: ' . $e->getMessage());
        }
    }
}
