<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EmployeeEventStatsService
{
    private function baseCountQuery(): \Illuminate\Database\Query\Builder
    {
        return DB::table('employee_events as ee1')
            ->whereRaw('ee1.id = (
                SELECT ee2.id FROM employee_events as ee2
                WHERE ee2.employee_id = ee1.employee_id
                ORDER BY ee2.event_date DESC
                LIMIT 1
            )');
    }

    private function applyTypesToList(\Illuminate\Database\Query\Builder $query, string|array $types): \Illuminate\Database\Query\Builder
    {
        return is_array($types)
            ? $query->whereIn('ev.event_type', $types)
            : $query->where('ev.event_type', $types);
    }

    /**
     * Фильтр по роли (Rep/RM/FFM/...) — та же логика, что и в baseListQuery()
     * и в графиках дашборда: роль берётся с ПОСЛЕДНЕЙ территории сотрудника
     * (скалярный подзапрос с тай-брейком по id, чтобы не задваивать при
     * одинаковом assigned_at), а не из статичного employees.position.
     * $employeeIdColumn — квалифицированное имя колонки employee_id в основном
     * запросе (разные алиасы у count- и list-запросов).
     */
    private function applyRoles(\Illuminate\Database\Query\Builder $query, array $roles, string $employeeIdColumn): \Illuminate\Database\Query\Builder
    {
        if (empty($roles)) {
            return $query;
        }

        return $query->whereRaw('(
            SELECT t.role
            FROM employee_territory et
            JOIN territories t ON t.id = et.territory_id
            WHERE et.employee_id = ' . $employeeIdColumn . '
            ORDER BY et.assigned_at DESC, et.id DESC
            LIMIT 1
        ) IN (' . implode(',', array_fill(0, count($roles), '?')) . ')', $roles);
    }

    /**
     * Общий хвост для counts*() ниже: группирует по event_type и отдаёт
     * ['hired' => N, 'dismissed' => N, ...] ОДНИМ запросом вместо одного
     * count() на каждый тип — важно, т.к. основная БД у нас на удалённом
     * сервере (192.168.33.39) и каждый лишний round-trip заметно ощутим
     * (карточки дашборда дёргают эти методы через AJAX на каждый клик).
     */
    private function countsGrouped(\Illuminate\Database\Query\Builder $query, array $types): array
    {
        $counts = $query->whereIn('ee1.event_type', $types)
            ->selectRaw('ee1.event_type, COUNT(*) as cnt')
            ->groupBy('ee1.event_type')
            ->pluck('cnt', 'event_type');

        return collect($types)->mapWithKeys(fn ($type) => [$type => (int) ($counts[$type] ?? 0)])->all();
    }

    public function countsWithLatestEvent(array $types, array $roles = []): array
    {
        $query = $this->applyRoles($this->baseCountQuery(), $roles, 'ee1.employee_id');

        return $this->countsGrouped($query, $types);
    }

    public function countsByMonth(array $types, int $month, int $year, array $roles = []): array
    {
        $query = $this->baseCountQuery()
            ->whereMonth('ee1.event_date', $month)
            ->whereYear('ee1.event_date', $year);
        $query = $this->applyRoles($query, $roles, 'ee1.employee_id');

        return $this->countsGrouped($query, $types);
    }

    public function countsByYear(array $types, int $year, array $roles = []): array
    {
        $query = $this->baseCountQuery()
            ->whereYear('ee1.event_date', $year);
        $query = $this->applyRoles($query, $roles, 'ee1.employee_id');

        return $this->countsGrouped($query, $types);
    }

    /**
     * Считает события заданных типов, у которых event_date попадает в период —
     * без ограничения "только последнее событие сотрудника" (в отличие от
     * countsByMonth/countsByYear), т.к. за произвольный период нас интересуют
     * все случившиеся события этого типа, даже если статус сотрудника с тех пор менялся.
     */
    public function countsByDateRange(array $types, string $from, string $to, array $roles = []): array
    {
        $query = DB::table('employee_events as ee1')
            ->whereBetween('ee1.event_date', [$from, $to]);
        $query = $this->applyRoles($query, $roles, 'ee1.employee_id');

        return $this->countsGrouped($query, $types);
    }

    private function baseListQuery(): \Illuminate\Database\Query\Builder
    {
        // Должность берём с последней территории сотрудника (та же логика,
        // что и везде в приложении), а не из статичного employees.position,
        // которое может не совпадать с текущей ролью после переназначения.
        // Скалярный подзапрос (не JOIN) — чтобы при нескольких записях
        // employee_territory с одинаковым assigned_at строки не задваивались.
        $roleSubquery = "(
            SELECT t.role
            FROM employee_territory et
            JOIN territories t ON t.id = et.territory_id
            WHERE et.employee_id = e.id
            ORDER BY et.assigned_at DESC, et.id DESC
            LIMIT 1
        ) as territory_role";

        return DB::table('employees as e')
            ->join('employee_events as ev', 'ev.employee_id', '=', 'e.id')
            ->select('e.*', 'ev.event_type', 'ev.event_date')
            ->selectRaw($roleSubquery)
            ->orderBy('ev.event_date', 'DESC');
    }

    public function getWithLatestEvent(string|array $types): Collection
    {
        $query = $this->baseListQuery()
            ->whereRaw('ev.id = (
                SELECT ee.id FROM employee_events ee
                WHERE ee.employee_id = ev.employee_id
                ORDER BY ee.event_date DESC
                LIMIT 1
            )');

        return $this->applyTypesToList($query, $types)->get();
    }

    public function getByMonth(string|array $types, int $month, int $year): Collection
    {
        $query = $this->baseListQuery()
            ->whereMonth('ev.event_date', $month)
            ->whereYear('ev.event_date', $year);

        return $this->applyTypesToList($query, $types)->get();
    }

    public function getByYear(string|array $types, int $year): Collection
    {
        $query = $this->baseListQuery()
            ->whereYear('ev.event_date', $year);

        return $this->applyTypesToList($query, $types)->get();
    }

    public function getByDateRange(string|array $types, string $from, string $to): Collection
    {
        $query = $this->baseListQuery()
            ->whereBetween('ev.event_date', [$from, $to]);

        return $this->applyTypesToList($query, $types)->get();
    }

    /**
     * Как getByDateRange(), но фильтрует по updated_at (когда запись реально
     * появилась/изменилась в БД), а не по event_date (когда событие произошло
     * по факту). Нужно для еженедельного дайджеста: админ иногда отмечает
     * увольнение постфактум (event_date — месяцы назад), и такая запись должна
     * попасть в ближайший отчёт, а не быть пропущена из-за старой даты события.
     */
    public function getByUpdatedRange(string|array $types, string $from, string $to): Collection
    {
        $query = $this->baseListQuery()
            ->whereBetween('ev.updated_at', [$from, $to]);

        return $this->applyTypesToList($query, $types)->get();
    }

    /**
     * Собирает Excel-файл (ФИО / ФИО англ / Должность / Почта / Тип события / Дата)
     * из коллекции, возвращённой getByDateRange/getByMonth/getByYear/getWithLatestEvent.
     * Используется и для ручного скачивания (DashboardController), и для плановых
     * email-рассылок (см. SendWeeklyDismissedReport). Возвращает абсолютный путь к файлу.
     */
    public function buildEventsExcelFile(Collection $employees, string $title): string
    {
        $eventLabels = [
            'hired'             => 'Принят',
            'dismissed'         => 'Уволен',
            'maternity_leave'   => 'В декрете',
            'return_from_leave' => 'Вышел из декрета',
            'change_position'   => 'Смена должности',
            'long_vacation'     => 'Длительный отпуск',
            'new'               => 'Новый',
        ];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'ФИО');
        $sheet->setCellValue('B1', 'ФИО англ');
        $sheet->setCellValue('C1', 'Должность');
        $sheet->setCellValue('D1', 'Почта');
        $sheet->setCellValue('E1', 'Тип события');
        $sheet->setCellValue('F1', 'Дата');

        $row = 2;
        foreach ($employees as $employee) {
            $sheet->setCellValue('A' . $row, $employee->full_name);
            $sheet->setCellValue('B' . $row, trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')));
            $sheet->setCellValue('C' . $row, $employee->territory_role ?? '');
            $sheet->setCellValue('D' . $row, $employee->email ?? '');
            $sheet->setCellValue('E' . $row, $eventLabels[$employee->event_type] ?? $employee->event_type);
            $sheet->setCellValue('F' . $row, \Carbon\Carbon::parse($employee->event_date)->format('d.m.Y'));
            $row++;
        }

        foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'events_' . \Illuminate\Support\Str::slug($title) . '_' . now()->format('Y-m-d_H-i') . '.xlsx';
        $filePath = storage_path('app/' . $fileName);

        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($filePath);

        return $filePath;
    }
}
