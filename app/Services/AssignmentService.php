<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Tablet;

class AssignmentService
{
    public function tabletAssignmentWithPdf(Employee $employee, Tablet $tablet, string $path, ?string $assignedAt): void
    {
        $employee->employee_tablet()->updateExistingPivot($tablet->id, [
            'confirmed' => true,
            'pdf_path' => $path,
            'assigned_at' => $assignedAt
        ]);

        $employee->setStatus('active');
    }

    public function confirmTablet(Employee $employee, Tablet $tablet, ?string $assignedAt): void
    {
        $employee->employee_tablet()->updateExistingPivot($tablet->id, [
            'confirmed' => true,
            // 'pdf_path' => $path,
            // 'assigned_at' => $assignedAt
        ]);

        $employee->setStatus('active');
    }


}
