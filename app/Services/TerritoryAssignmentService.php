<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Territory;
use Illuminate\Support\Facades\DB;

class TerritoryAssignmentService {
    public function unassign(Employee $employee, Territory $territory, $unassignedAt = null)
    {
        $assignmentToRemove = DB::table('employee_territory')
            ->where('employee_id', $employee->id)
            ->where('territory_id', $territory->id)
            ->where('confirmed', 0)
            ->orderByDesc('id')
            ->first();

        $territory->employee()->dissociate();
        $territory->save();

        if ($assignmentToRemove) {
            DB::table('employee_territory')
                ->where('id', $assignmentToRemove->id)
                ->delete();

            return 'removed';
        } else {
            $employee->employee_territory()->updateExistingPivot(
                $territory->id,
                ['unassigned_at' => $unassignedAt]
            );

            $territory->old_employee_id = $employee->full_name;
            $territory->save();

            return 'unassigned';
        }
    }
}
