<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExcelDataUploadRequest;
use App\Models\Employee;
use App\Models\EmployeeTablet;
use App\Models\Tablet;
use App\Services\ExcelUploadService;
use Carbon\Carbon;

class ExcelDataUploadController extends Controller
{
    public function __construct(private ExcelUploadService $uploader) {}

    public function uploadTabletsAssignment(ExcelDataUploadRequest $request)
    {
        try {
            $rows = $this->uploader->dataRows(
                $this->uploader->loadRows($request, 'tablets_assignments')
            );

            foreach ($rows as $row) {
                $tabletId   = $row[1] ?? null;
                $employeeId = isset($row[2]) && is_numeric($row[2]) ? (int) $row[2] : null;

                if (!is_numeric($tabletId)) {
                    $tablet   = Tablet::where('model', $tabletId)->first();
                    $tabletId = $tablet?->id;
                }

                if (is_null($tabletId)) {
                    return back()->withErrors(['error' => 'Ошибка: tablet_id отсутствует или не найден в базе']);
                }

                EmployeeTablet::firstOrCreate(
                    ['tablet_id' => $tabletId, 'employee_id' => $employeeId],
                    [
                        'assigned_at' => $this->formatDate($row[3]),
                        'returned_at' => $this->formatDate($row[4]),
                        'confirmed'   => $row[5] ?? null,
                    ]
                );
            }

            return back()->with('success', 'Данные успешно загружены!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Ошибка обработки файла: ' . $e->getMessage()]);
        }
    }

    public function uploadTablets(ExcelDataUploadRequest $request)
    {
        try {
            $rows = $this->uploader->dataRows(
                $this->uploader->loadRows($request, 'tablets')
            );

            foreach ($rows as $row) {
                Tablet::firstOrCreate(
                    ['serial_number' => $row[3]],
                    [
                        'id'                    => $row[0],
                        'model'                 => $row[1] ?? null,
                        'invent_number'         => $row[2] ?? null,
                        'imei'                  => $row[4] ?? null,
                        'beeline_number'        => $row[5] ?? null,
                        'beeline_number_status' => $row[6] ?? null,
                        'status'                => $row[7] ?? null,
                        'old_employee_id'       => is_numeric($row[8]) ? $row[8] : null,
                        'employee_id'           => is_numeric($row[9]) ? $row[9] : null,
                    ]
                );
            }

            return back()->with('success', 'Данные успешно загружены!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Ошибка обработки файла: ' . $e->getMessage()]);
        }
    }

    public function uploadEmployees(ExcelDataUploadRequest $request)
    {
        try {
            $rows = $this->uploader->dataRows(
                $this->uploader->loadRows($request, 'employees')
            );

            foreach ($rows as $row) {
                Employee::updateOrCreate(
                    ['id' => $row[0]],
                    [
                        'full_name'    => $row[1] ?? null,
                        'first_name'   => $row[2] ?? null,
                        'last_name'    => $row[3] ?? null,
                        'birth_date'   => !empty($row[4]) ? Carbon::createFromFormat('d/m/Y', $row[4])->format('Y-m-d') : null,
                        'email'        => $row[5] ?? null,
                        'hiring_date'  => !empty($row[6]) ? Carbon::createFromFormat('d/m/Y', $row[6])->format('Y-m-d') : null,
                        'firing_date'  => !empty($row[7]) ? Carbon::createFromFormat('d/m/Y', $row[7])->format('Y-m-d') : null,
                        'position'     => $row[8] ?? null,
                        'status'       => $row[9] ?? null,
                    ]
                );
            }

            return back()->with('success', 'Данные успешно загружены!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Ошибка обработки файла: ' . $e->getMessage()]);
        }
    }

    private function formatDate(?string $date): ?string
    {
        return $date && strtotime($date) ? date('Y-m-d', strtotime($date)) : null;
    }
}
