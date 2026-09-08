<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeCrmId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CrmMappingController extends Controller
{
    // CRM employees: [{employee_id, employee, employee_position}]
    private function getCrmEmployees(): array
    {
        return Cache::remember('crm_employees_list', 3600, function () {
            try {
                return DB::connection('nobel')
                    ->select('SELECT employee_id, TRIM(employee) as employee, employee_position
                              FROM qs_calls
                              WHERE employee_id IS NOT NULL AND employee IS NOT NULL AND employee <> ""
                              GROUP BY employee_id, employee, employee_position
                              ORDER BY employee');
            } catch (\Exception $e) {
                return [];
            }
        });
    }

    public function index()
    {
        $crmEmployees = $this->getCrmEmployees();

        // Все сотрудники системы — для поиска в комбобоксе
        $sysEmployees = Employee::orderBy('full_name')->get(['id', 'full_name', 'position']);

        // crm_employee_id => EmployeeCrmId (с подгруженным сотрудником).
        // crm_employee_id уникален в этой таблице, поэтому keyBy тут безопасен —
        // это направление связи остаётся 1:1, many-to-one работает в обратную сторону
        // (у одного сотрудника может быть несколько таких строк с разными crm_employee_id).
        $crmLinks = EmployeeCrmId::with('employee:id,full_name,position')->get()->keyBy('crm_employee_id');

        $crmTotal = count($crmEmployees);
        $mapped   = $crmLinks->count();

        return view('admin.crm-mapping', compact(
            'crmEmployees', 'sysEmployees', 'crmLinks', 'crmTotal', 'mapped'
        ));
    }

    // Привязать CRM-аккаунт (employee_id из qs_calls) к сотруднику системы.
    // Один сотрудник системы может иметь несколько таких привязок (many-to-one) —
    // например, если CRM завела новую учётку при повторном найме.
    public function link(Request $request)
    {
        $request->validate([
            'crm_employee_id' => 'required|integer',
            'employee_id'     => 'nullable|integer|exists:employees,id',
        ]);

        $crmId = (int) $request->input('crm_employee_id');

        // Снимаем текущую привязку именно этого CRM-аккаунта (если была) —
        // остальные аккаунты, привязанные к тому же или другому сотруднику, не трогаем.
        EmployeeCrmId::where('crm_employee_id', $crmId)->delete();

        if ($request->filled('employee_id')) {
            $emp = Employee::findOrFail($request->input('employee_id'));
            EmployeeCrmId::create([
                'employee_id'     => $emp->id,
                'crm_employee_id' => $crmId,
                'confirmed'       => true,
            ]);
            return back()->with('success', "CRM-аккаунт привязан к «{$emp->full_name}».");
        }

        return back()->with('success', 'Привязка сброшена.');
    }

    public function autoMatch()
    {
        $crmEmployees = $this->getCrmEmployees();

        // Lookup: первые два слова CRM-имени => crm_employee_id
        $crmByShName = [];
        foreach ($crmEmployees as $r) {
            $parts = preg_split('/\s+/', trim($r->employee));
            $sh = implode(' ', array_slice($parts, 0, 2));
            $crmByShName[$sh] = (int) $r->employee_id;
        }

        // CRM-аккаунты, уже привязанные к кому бы то ни было — не переопределяем
        $alreadyLinked = EmployeeCrmId::pluck('crm_employee_id')->flip();

        $matched = 0;
        foreach (Employee::all() as $emp) {
            $shName = $emp->sh_name;
            if (!$shName) continue;

            if (isset($crmByShName[$shName])) {
                $crmId = $crmByShName[$shName];
                if ($alreadyLinked->has($crmId)) continue;

                EmployeeCrmId::create([
                    'employee_id'     => $emp->id,
                    'crm_employee_id' => $crmId,
                    'confirmed'       => false,
                ]);
                $alreadyLinked->put($crmId, true);
                $matched++;
            }
        }

        return back()->with('success', "Автоматически привязано: {$matched} сотрудников.");
    }
}
