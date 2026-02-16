<?php

namespace App\Http\Controllers;

use App\Services;
use App\Models\Tablet;
use App\Models\Employee;
use App\Models\Territory;
use Illuminate\Http\Request;
use App\Models\EmployeeTablet;

use App\Services\PdfUploadService;
use Illuminate\Support\Facades\DB;
use App\Services\AssignmentService;
use App\Services\TabletAssignmentService;

class EmployeeTabletController extends Controller
{
    // public function showForm()
    // {
    //     return view('components/upload-pdf');
    // }

    protected $tabletAssignmentService;

    public function __construct(TabletAssignmentService $tabletAssignmentService){
        $this->tabletAssignmentService = $tabletAssignmentService;
    }

    public function assignTablet(Request $request, Employee $employee){
        $result = $this->tabletAssignmentService->assignTablet($request, $employee);
        return redirect()->back()->with('success', $result['success']);
    }

    public function unassignTablet(Employee $employee, Tablet $tablet, Request $request){
        $returned_at = $request->input('returned_at');
        $result = $this->tabletAssignmentService->unassignTablet($employee, $tablet, $returned_at, $request);
        return redirect()->back()->with('success', $result['success']);
    }

    public function confirmTablet(Employee $employee, Tablet $tablet){
        $result = $this->tabletAssignmentService->confirmTablet($employee, $tablet);
        return redirect()->back()->with('success', $result['success']);
    }

    // Добавление планшета к сотруднику через страницу планшета
    public function assignEmployee2(Request $request, Tablet $tablet)
    {
        // Найти сотрудника по переданному ID
        $employee = Employee::findOrFail($request->input('employee_id'));

        // Привязать сотрудника к территории
        $tablet->employee()->associate($employee);
        $tablet->save();

        // Запись в таблицу `employee_territory`
        $employee->employee_tablet  ()->attach($tablet->id, ['assigned_at' => $request->input('assigned_at')]);

        return redirect()->back()->with('success', 'Employee successfully assigned to the tablet.');
    }




    public function assignTabletWithPdf(Request $request, Employee $employee, Tablet $tablet)
    {
        // $request->validate([
        //     'pdf_file' => 'required|mimes:pdf|max:2048',
        // ]);
        $file = $request->file('pdf_file');
        $assignmentDate = $request->input('assigned_at');


        $path = $this->tabletAssignmentService->assignTabletWithPdf($file, $employee, $tablet, $assignmentDate);

        // $this->assignmentService->tabletAssignmentWithPdf($employee, $tablet, $path, $assignmentDate);

        return back()->with('success', 'Файл успешно загружен!');
    }

    public function download($id)
    {
        $assignment = EmployeeTablet::findOrFail($id);
        return response()->download(storage_path("app/public/{$assignment->pdf_path}"));
    }



    public function printAct(Employee $employee, Tablet $tablet)
    {
        // Получаем PDF для планшета
        $pdfAssignment = DB::table('employee_tablet')
            ->where('employee_id', $employee->id)
            ->where('tablet_id', $tablet->id)
            ->select('id', 'pdf_path', 'confirmed')
            ->orderByDesc('id')
            ->first();

        // Проверка наличия pdfAssignment
        $hasPdf = $pdfAssignment && $pdfAssignment->pdf_path;
        $tabletConf = $pdfAssignment && $pdfAssignment->confirmed;

        // Данные, которые будут переданы в представление
        return view('print-act', [
            'employee' => $employee,
            'tablet' => $tablet,
            'hasPdf' => $hasPdf, // Флаг для наличия pdf
            'pdfAssignment' => $pdfAssignment, // Для использования в компоненте
            'tabletConf' => $tabletConf,
            'showHeader' => false,
            'printPadding' => 1
        ]);
    }

    public function printAct2(Employee $employee, Tablet $tablet)
    {
        // Получаем PDF для планшета
        $pdfAssignment = DB::table('employee_tablet')
            ->where('employee_id', $employee->id)
            ->where('tablet_id', $tablet->id)
            ->select('id', 'pdf_path', 'confirmed')
            ->orderByDesc('id')
            ->first();

        // Проверка наличия pdfAssignment
        $hasPdf = $pdfAssignment && $pdfAssignment->pdf_path;
        $tabletConf = $pdfAssignment && $pdfAssignment->confirmed;

        // Данные, которые будут переданы в представление
        return view('print-act2', [
            'employee' => $employee,
            'tablet' => $tablet,
            'hasPdf' => $hasPdf, // Флаг для наличия pdf
            'pdfAssignment' => $pdfAssignment, // Для использования в компоненте
            'tabletConf' => $tabletConf,
            'showHeader' => false,
            'printPadding' => 1
        ]);
    }


}
