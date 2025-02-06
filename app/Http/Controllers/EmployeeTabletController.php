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
        return $this->unassignTablet($employee, $tablet);
    }


    public function download($id)
    {
        $assignment = EmployeeTablet::findOrFail($id);
        return response()->download(storage_path("app/public/{$assignment->pdf_path}"));
    }

    public function unassignTablet(Employee $employee, Tablet $tablet){
        $pdfAssignment = DB::table('employee_tablet')
            ->where('employee_id', $employee->id)
            ->where('tablet_id', $tablet->id)
            ->where('confirmed', 0)
            ->orderByDesc('id') // Берем только поле pdf_path
            ->first();

        $pdfUnassignment = DB::table('employee_tablet')
            ->where('employee_id', $employee->id)
            ->where('tablet_id', $tablet->id)
            ->orderByDesc('id') // Берем только поле pdf_path
            ->first();


        $tablet->employee()->dissociate();
        $tablet->save();

        if($pdfAssignment) {
            DB::table('employee_tablet')
            ->where('id', $pdfAssignment->id) // Указываем ID найденной записи
            ->delete();

            return redirect()->back()->with('success', 'Tablet unassigned and removed due to unconfirmed status.');
        } else {
            // if (!$pdfUnassignment || empty($pdfUnassignment->unassign_pdf)) {
            //     return redirect()->back()->with('error', 'Для отвязки планшета необходимо загрузить PDF.');
            // }
            $tablet->old_employee_id = $employee->full_name;
            $tablet->save();
            $employee->employee_tablet()->updateExistingPivot($tablet->id, ['returned_at' => now()]);
            return redirect()->back()->with('success', 'Tablet successfully unassigned from the employee.');
        }


    }

    public function assignTablet(Request $request, Employee $employee){
        // $employee = Employee::findOrFail($employee->id);
        $tablet = Tablet::findOrFail($request->input('tablet_id'));

        $tablet->employee()->associate($employee);
        // $tablet->employee_id = $employee->id;
        $tablet->save();
        // Filling in employee_territory table
        $employee->employee_tablet()->attach($tablet->id, ['assigned_at' => now()]);



        return redirect()->back()->with('success', 'Tablet successfully assigned to the employee.');
    }

    public function printAct(Employee $employee, Tablet $tablet)
    {
        // Получаем PDF для планшета
        $pdfAssignment = DB::table('employee_tablet')
            ->where('employee_id', $employee->id)
            ->where('tablet_id', $tablet->id)
            ->select('id', 'pdf_path')
            ->orderByDesc('id')
            ->first();

        // Проверка наличия pdfAssignment
        $hasPdf = $pdfAssignment && $pdfAssignment->pdf_path;

        // Данные, которые будут переданы в представление
        return view('print-act', [
            'employee' => $employee,
            'tablet' => $tablet,
            'hasPdf' => $hasPdf, // Флаг для наличия pdf
            'pdfAssignment' => $pdfAssignment, // Для использования в компоненте
            'showHeader' => false,
            'printPadding' => 1
        ]);
    }


}
