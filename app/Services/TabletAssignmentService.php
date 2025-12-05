<?php

namespace App\Services;

use App\Models\Tablet;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TabletAssignmentService
{
    public function assignTablet(Request $request, Employee $employee)
    {
        // Валидируем input
        $validatedData = $request->validate([
            'tablet_id' => 'required|exists:tablets,id',
            'assigned_at' => 'required|date',
        ]);

        // Получаем планшет
        $tablet = Tablet::findOrFail($validatedData['tablet_id']);

        // Проверяем, если планшет уже назначен сотруднику
        // if ($tablet->employee_id) {
        //     throw ValidationException::withMessages(['error' => 'This tablet is already assigned to another employee.']);
        // }

        // Назначаем планшет сотруднику
        $tablet->employee()->associate($employee);
        $tablet->save();

        // Записываем в таблицу связей employee_tablet
        $employee->employee_tablet()->attach($tablet->id, ['assigned_at' => $validatedData['assigned_at']]);

        return ['success' => 'Tablet successfully assigned to the employee.'];
    }

    public function unassignTablet(Employee $employee, Tablet $tablet, Request $request){
        // $request->validate([
        //     'unassign_pdf' => 'required|mimes:pdf|max:2048',
        //     'returned_at' => 'required|max:2048',
        // ]);

        $path = $request->hasFile('unassign_pdf') ? $request->file('unassign_pdf')->store('uploads/unassign', 'public') : null;
        $returned_at = $request->input('returned_at');


        $pdfAssignment = DB::table('employee_tablet')
            ->where('employee_id', $employee->id)
            ->where('tablet_id', $tablet->id)
            ->where('confirmed', 0)
            ->orderByDesc('id') // Берем только поле pdf_path
            ->first();

        // $tablet->currentAssignment()->delete();
        // $tablet->refresh();


        $tablet->employee()->dissociate();
        $tablet->save();

        if($pdfAssignment) {
            DB::table('employee_tablet')
            ->where('id', $pdfAssignment->id) // Указываем ID найденной записи
            ->delete();

            return ['success', 'Tablet unassigned and removed due to unconfirmed status.'];
        } else {
            $tablet->old_employee_id = $employee->full_name;
            $tablet->save();
            $employee->employee_tablet()->updateExistingPivot($tablet->id, [
                'returned_at' => $returned_at,
                'unassign_pdf' => $path
            ]);
            return ['success' => 'Tablet successfully unassigned from the employee.'];
        }
    }

    public function confirmTablet(Employee $employee, Tablet $tablet){
        $assignment = $employee->employee_tablet()->where('tablet_id', $tablet->id)->first();
        if (!$assignment) {
            return redirect()->back()->with('error', 'Tablet assignment not found.');
        }
        // Обновляем запись
        $employee->employee_tablet()->updateExistingPivot($tablet->id, [
            'confirmed' => true
        ]);

        return ['success' => 'Tablet confirmed.'];
    }

    public function assignTabletWithPdf(?UploadedFile $file, Employee $employee, Tablet $tablet, ?string $assignedAt): ?string
    {
        // Генерация имени файла
        $filename = "Передача_{$employee->first_name}_{$employee->last_name}_{$tablet->serial_number}_{$assignedAt}.pdf";

        // Сохранение файла
        $path = $file ? $file->storeAs('uploads/assign', $filename, 'public') : null;

        // Обновление записи в связующей таблице
        $employee->employee_tablet()->updateExistingPivot($tablet->id, [
            'confirmed' => true,
            'pdf_path' => $path,
            'assigned_at' => $assignedAt,
        ]);

        // Возврат пути
        return $path;
    }
}
