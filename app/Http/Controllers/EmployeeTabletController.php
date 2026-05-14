<?php

namespace App\Http\Controllers;

use App\Models\Tablet;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\EmployeeTablet;
use Illuminate\Support\Facades\DB;
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

    // TabletController
    public function cityCheck(Request $request)
    {
        $employeeId = $request->input('employee_id');
        $tabletId   = $request->input('tablet_id');

        $employee = Employee::findOrFail($employeeId);
        $tablet   = Tablet::with('responsible')->findOrFail($tabletId);

        $employeeCity  = $employee->employee_territory()
            ->whereNull('unassigned_at')
            ->latest('assigned_at')
            ->first()?->city;

        $responsibleCity = $tablet->responsible?->employee_territory()
            ->whereNull('unassigned_at')
            ->latest('assigned_at')
            ->first()?->city;

        return response()->json([
            'employee_city'   => $employeeCity,
            'responsible_city'=> $responsibleCity,
            'match'           => $employeeCity && $responsibleCity && $employeeCity === $responsibleCity,
        ]);
    }

    // Добавление/переназначение планшета через страницу планшета
    public function assignEmployee2(Request $request, Tablet $tablet)
    {
        $employee   = Employee::findOrFail($request->input('employee_id'));
        $assignedAt = $request->input('assigned_at', now()->format('Y-m-d'));

        // Находим текущего активного сотрудника планшета
        $currentPivot = DB::table('employee_tablet')
            ->where('tablet_id', $tablet->id)
            ->whereNull('returned_at')
            ->orderByDesc('id')
            ->first();

        // Снимаем его через сервис — логика та же что и при обычном снятии:
        // не подтверждена → удаляет запись, подтверждена → ставит returned_at
        if ($currentPivot) {
            $currentEmployee = Employee::find($currentPivot->employee_id);
            if ($currentEmployee) {
                $this->tabletAssignmentService->unassignTablet(
                    $currentEmployee,
                    $tablet,
                    $assignedAt
                );
            }
        }

        // Привязываем нового сотрудника
        $tablet->employee()->associate($employee);
        $tablet->save();

        $employee->employee_tablet()->attach($tablet->id, ['assigned_at' => $assignedAt]);

        return redirect()->back()->with('success', 'Планшет переназначен.');
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
        return $this->renderAct($employee, $tablet, 'print-act');
    }

    public function printAct2(Employee $employee, Tablet $tablet)
    {
        return $this->renderAct($employee, $tablet, 'print-act2');
    }

    private function renderAct(Employee $employee, Tablet $tablet, string $view): \Illuminate\View\View
    {
        $pdfAssignment = DB::table('employee_tablet')
            ->where('employee_id', $employee->id)
            ->where('tablet_id', $tablet->id)
            ->select('id', 'pdf_path', 'confirmed')
            ->orderByDesc('id')
            ->first();

        return view($view, [
            'employee'      => $employee,
            'tablet'        => $tablet,
            'hasPdf'        => (bool) ($pdfAssignment?->pdf_path),
            'pdfAssignment' => $pdfAssignment,
            'tabletConf'    => (bool) ($pdfAssignment?->confirmed),
            'showHeader'    => false,
            'printPadding'  => 1,
        ]);
    }


}
