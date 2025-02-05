<?php

namespace App\Http\Controllers;

use App\Models\Tablet;
use App\Models\Employee;
use App\Models\Territory;
use Illuminate\Http\Request;
use App\Models\EmployeeTablet;
use Illuminate\Support\Facades\DB;

class EmployeeTabletController extends Controller
{
    // public function showForm()
    // {
    //     return view('components/upload-pdf');
    // }
    protected $employeeController;

    public function __construct(EmployeeController $employeeController)
    {
        $this->employeeController = $employeeController;
    }

    public function uploadAssignPdf(Request $request, Employee $employee, Tablet $tablet){
        $request->validate([
            'pdf_file' => 'required|mimes:pdf|max:2048',
        ]);

        // Сохранение файла в storage/app/public/uploads
        $path = $request->file('pdf_file')->store('uploads/assign', 'public');

        // Обновление записи в пивотной таблице employee_tablet
        // EmployeeTablet::where('employee_id', $employee->id)
        // ->where('tablet_id', $tablet->id)
        // ->update([
        //     'confirmed' => true,
        //     'pdf_path' => $path,
        // ]);

        $employee->employee_tablet()->updateExistingPivot($tablet->id, [
            'confirmed' => true,
            'pdf_path' => $path
        ]);

        $pdfAssignment = DB::table('employee_tablet')
                ->where('employee_id', $employee->id)
                ->where('tablet_id', $tablet->id)
                ->select('pdf_path')
                ->orderByDesc('id') // Берем только поле pdf_path
                ->first();


        return back()->with('success', 'Файл успешно загружен!');
        // return view('employee', compact('pdf_path', 'employee'));

        // return view('employee', compact('pdf_path'));
    }

    // public function confirmTerritory(Employee $employee, Territory $territory){
    //     $assignment = $employee->employee_territory()->where('territory_id', $territory->id)->first();
    //     if (!$assignment) {
    //         return redirect()->back()->with('error', 'Territory assignment not found.');
    //     }

    //     // Обновляем запись
    //     $employee->employee_territory()->updateExistingPivot($territory->id, ['confirmed' => true]);

    //     return redirect()->back()->with('success', 'Territory confirmed.');
    // }

    public function uploadUnassignPdf(Request $request, Employee $employee, Tablet $tablet){
        $request->validate([
            'unassign_pdf' => 'required|mimes:pdf|max:2048',
        ]);

        // Сохранение файла
        $path = $request->file('unassign_pdf')->store('uploads/unassign', 'public');

        // Обновление записи в пивотной таблице employee_tablet
        $employee->employee_tablet()->updateExistingPivot($tablet->id, [
            'unassign_pdf' => $path,
        ]);

        // return back()->with('success', 'Файл успешно загружен! Теперь можно отвязать планшет.');
        return $this->employeeController->unassignTablet($employee, $tablet);
    }


    public function download($id)
    {
        $assignment = EmployeeTablet::findOrFail($id);
        return response()->download(storage_path("app/public/{$assignment->pdf_path}"));
    }
}
