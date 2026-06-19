<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CrmMappingController extends Controller
{
    // CRM employees: [{employee_id, employee, employee_position}]
    private function getCrmEmployees(): array
    {
        return DB::connection('nobel')
            ->select('SELECT DISTINCT employee_id, TRIM(employee) as employee, employee_position
                      FROM qs_calls
                      WHERE employee_id IS NOT NULL AND employee IS NOT NULL AND employee <> ""
                      ORDER BY employee');
    }

    public function index()
    {
        $crmEmployees = $this->getCrmEmployees();

        // Main system employees for dropdown (id => full_name)
        $sysEmployees = Employee::orderBy('full_name')
            ->get(['id', 'full_name', 'position', 'crm_employee_id']);

        // Build reverse map: crm_employee_id => Employee
        $linkedByCrmId = $sysEmployees->whereNotNull('crm_employee_id')
            ->keyBy('crm_employee_id');

        $crmTotal = count($crmEmployees);
        $mapped   = $linkedByCrmId->count();

        return view('admin.crm-mapping', compact(
            'crmEmployees', 'sysEmployees', 'linkedByCrmId', 'crmTotal', 'mapped'
        ));
    }

    // Link a main system employee to a CRM employee_id
    public function link(Request $request)
    {
        $request->validate([
            'crm_employee_id' => 'required|integer',
            'employee_id'     => 'nullable|integer|exists:employees,id',
        ]);

        $crmId = (int) $request->input('crm_employee_id');

        // Unlink any employee that previously had this crm_employee_id
        Employee::where('crm_employee_id', $crmId)->update(['crm_employee_id' => null]);

        if ($request->filled('employee_id')) {
            $emp = Employee::findOrFail($request->input('employee_id'));
            $emp->update(['crm_employee_id' => $crmId]);
            return back()->with('success', "CRM-сотрудник привязан к «{$emp->full_name}».");
        }

        return back()->with('success', 'Привязка сброшена.');
    }

    public function autoMatch()
    {
        $crmEmployees = $this->getCrmEmployees();

        // Build lookup: first two words of CRM name => crm_employee_id
        $crmByShName = [];
        foreach ($crmEmployees as $r) {
            $parts = preg_split('/\s+/', trim($r->employee));
            $sh = implode(' ', array_slice($parts, 0, 2));
            $crmByShName[$sh] = (int) $r->employee_id;
        }

        // Already linked crm_employee_ids (don't overwrite)
        $alreadyLinked = Employee::whereNotNull('crm_employee_id')
            ->pluck('crm_employee_id')
            ->flip();

        $matched = 0;
        foreach (Employee::all() as $emp) {
            if ($emp->crm_employee_id) continue;
            $shName = $emp->sh_name;
            if (!$shName) continue;

            if (isset($crmByShName[$shName])) {
                $crmId = $crmByShName[$shName];
                if ($alreadyLinked->has($crmId)) continue;
                $emp->update(['crm_employee_id' => $crmId]);
                $alreadyLinked->put($crmId, true);
                $matched++;
            }
        }

        return back()->with('success', "Автоматически привязано: {$matched} сотрудников.");
    }
}
