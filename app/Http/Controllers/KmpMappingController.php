<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeKmpName;
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
            } catch (\Exception) {
                return [];
            }
        });
    }

    public function index()
    {
        $kmpEmployees = $this->getKmpEmployees();

        $sysEmployees = Employee::orderBy('full_name')->get(['id', 'full_name', 'position']);

        // kmp_employee_name => EmployeeKmpName (с подгруженным сотрудником).
        // kmp_employee_name уникально в этой таблице (1:1 в эту сторону), а у одного
        // сотрудника таких строк может быть несколько (many-to-one) — ровно случай
        // повторного найма с новой учёткой КМП, под который всё это и делается.
        $kmpLinks = EmployeeKmpName::with('employee:id,full_name,position')->get()->keyBy('kmp_employee_name');

        $kmpTotal = count($kmpEmployees);
        $mapped   = $kmpLinks->count();

        return view('admin.kmp-mapping', compact(
            'kmpEmployees', 'sysEmployees', 'kmpLinks', 'kmpTotal', 'mapped'
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

            // Снимаем текущую привязку именно этого имени КМП (если была) —
            // остальные имена, привязанные к тому же сотруднику, не трогаем.
            EmployeeKmpName::where('kmp_employee_name', $kmpName)->delete();

            if ($employeeId !== null && $employeeId !== '') {
                $emp = Employee::findOrFail((int) $employeeId);
                EmployeeKmpName::create([
                    'employee_id'       => $emp->id,
                    'kmp_employee_name' => $kmpName,
                    'confirmed'         => true,
                ]);
                return back()->with('success', "KMP-аккаунт привязан к «{$emp->full_name}».");
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
        $kmpEmployees = $this->getKmpEmployees();

        // Lookup: первые два слова KMP-имени => полное имя
        $kmpByShName = [];
        foreach ($kmpEmployees as $r) {
            $parts = preg_split('/\s+/', trim($r->name));
            $sh    = implode(' ', array_slice($parts, 0, 2));
            if (!isset($kmpByShName[$sh])) {
                $kmpByShName[$sh] = $r->name;
            }
        }

        // Имена КМП, уже привязанные к кому бы то ни было — не переопределяем
        $alreadyLinked = EmployeeKmpName::pluck('kmp_employee_name')->flip();

        $matched = 0;
        foreach (Employee::all() as $emp) {
            $shName = $emp->sh_name;
            if (!$shName) continue;

            if (isset($kmpByShName[$shName])) {
                $kmpName = $kmpByShName[$shName];
                if ($alreadyLinked->has($kmpName)) continue;

                EmployeeKmpName::create([
                    'employee_id'       => $emp->id,
                    'kmp_employee_name' => $kmpName,
                    'confirmed'         => false,
                ]);
                $alreadyLinked->put($kmpName, true);
                $matched++;
            }
        }

        return back()->with('success', "Автоматически привязано: {$matched} сотрудников.");
    }
}
