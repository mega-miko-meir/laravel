<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeCredentialsUpdateRequest;
use App\Http\Requests\EmployeeStoreRequest;
use App\Http\Requests\EmployeeUpdateRequest;
use App\Models\Employee;
use App\Models\EmployeeCredential;
use App\Models\EmployeeEvent;
use App\Models\Nobel\Call;
use App\Models\Nobel\Kmp;
use App\Notifications\EmployeeDeletedNotification;
use App\Services\TeamService;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Show the form for creating a new employee.
     *
     * @return \Illuminate\View\View
     */
    public function createEmployeeForm()
    {
        return view('create-edit-employee');
    }

    /**
     * Store a newly created employee.
     *
     * @param EmployeeStoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createEmployee(EmployeeStoreRequest $request)
    {
        $incomingFields = $request->validated();

        // Устанавливаем статус по умолчанию "new"
        $incomingFields['status'] = 'new';

        // Если дата найма не передана, устанавливаем текущую дату
        $incomingFields['hiring_date'] = $incomingFields['hiring_date'] ?? now();

        // Создаём сотрудника
        $employee = Employee::create($incomingFields);

        // Создаём событие "new"
        EmployeeEvent::create([
            'employee_id' => $employee->id,
            'event_type' => 'hired',
            'event_date' => $employee->hiring_date ?? now(),
        ]);

        return redirect()->route('employees.show', ['id' => $employee->id])
            ->with('success', 'Employee added successfully!');
    }

    /**
     * Display the specified employee.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function showEmployee(int $id)
    {
        $employee = Employee::with(['tablets', 'territories', 'employee_territory', 'employee_tablet', 'credentials', 'events', 'crmIds', 'kmpNames'])->findOrFail($id);

        $lastTerritory  = $employee->employee_territory()
            ->with(['children.employeeTerritories.employee'])
            ->latest('assigned_at')->first();
        $lastTablet     = $employee->employee_tablet()->withPivot('assigned_at', 'returned_at')->orderByDesc('assigned_at')->first();

        return view('employee', [
            'employee'             => $employee,
            'lastTerritory'        => $lastTerritory,
            'lastTablet'           => $lastTablet,
            'selectedBricks'       => $lastTerritory?->bricks ?? collect(),
            'bricks'               => \App\Models\Brick::all(),
            // Сортировка — по медпреду, который последним пользовался планшетом
            'availableTablets'     => \App\Models\Tablet::free()->with(['oldEmployee', 'latestAssignment.employee'])->get()
                ->sortBy(fn($t) => $t->latestAssignment?->employee?->full_name ?? '~')
                ->values(),
            // Сортировка — по региональному менеджеру (сотруднику на родительской территории)
            'availableTerritories' => \App\Models\Territory::whereNull('employee_id')
                ->with([
                    'employeeTerritories' => fn($q) => $q->with('employee')->latest('assigned_at'),
                    'parent.employee',
                ])
                ->get()
                ->sortBy(fn($t) => $t->parent?->employee?->full_name ?? '~')
                ->values(),
            'territoriesHistory'   => $employee->employee_territory()->withPivot('assigned_at', 'unassigned_at', 'id')->orderByDesc('assigned_at')->get(),
            'tabletHistories'      => \App\Models\EmployeeTablet::where('employee_id', $employee->id)->with('tablet')->orderByDesc('assigned_at')->get(),
            'currentStatus'        => $employee->events()->latest('event_date')->value('event_type'),
            // Наличие CRM/KMP-блоков определяется по локальным полям сотрудника —
            // без обращения к внешним БД, чтобы карточка открывалась мгновенно.
            // Сами данные подгружаются отдельными запросами (см. visitStatsPartial/kmpStatsPartial).
            'hasVisits'             => $employee->crmIds->isNotEmpty(),
            'hasKmp'                => $employee->kmpNames->isNotEmpty(),
        ]);
    }

    /**
     * Блок статистики визитов CRM для карточки сотрудника.
     * Грузится отдельным запросом (fetch on tab click), чтобы не блокировать открытие карточки.
     */
    public function visitStatsPartial(Employee $employee)
    {
        $stats = $this->getVisitStats($employee);

        if (!$stats) {
            return view('components.stats-unavailable', ['label' => 'визитам'])->render();
        }

        return view('components.visit-stats', ['stats' => $stats])->render();
    }

    /**
     * Блок статистики продаж KMP для карточки сотрудника.
     * Грузится отдельным запросом (fetch on tab click), чтобы не блокировать открытие карточки.
     */
    public function kmpStatsPartial(Employee $employee)
    {
        $stats = $this->getKmpStats($employee);

        if (!$stats) {
            return view('components.stats-unavailable', ['label' => 'продажам KMP'])->render();
        }

        return view('components.kmp-stats', ['stats' => $stats])->render();
    }

    private function getVisitStats(Employee $employee): ?array
    {
        $crmIds = $employee->crm_employee_ids;
        if (empty($crmIds)) {
            return null;
        }

        try {
            sort($crmIds);
            $employeeId = $employee->id;

            return \Illuminate\Support\Facades\Cache::remember(
                'employee_visit_stats_' . implode('-', $crmIds),
                3600,
                function () use ($crmIds, $employeeId) {
                    // whereIn по всем привязанным CRM-аккаунтам сотрудника —
                    // метрики агрегируются по истории найма/увольнения/повторного найма.
                    $base = Call::whereIn('employee_id', $crmIds)
                        ->where('appointment_status', 'Выполнено')
                        ->whereIn('appointment_type', ['Визит к врачу', 'Визит в аптеку']);

                    // 1 запрос вместо 5: все скалярные KPI
                    $kpi = (clone $base)->selectRaw('
                        COUNT(*) as total,
                        ROUND(AVG(CASE WHEN appointment_duration > 0 THEN appointment_duration END)) as avgDur,
                        MAX(appointment_Date) as lastDate,
                        SUM(appointment_type = "Визит к врачу") as doctorVisits,
                        SUM(appointment_type = "Визит в аптеку") as pharmacyVisits,
                        SUM(YEAR(appointment_Date) = YEAR(NOW()) AND MONTH(appointment_Date) = MONTH(NOW())) as thisMonth,
                        SUM(YEAR(appointment_Date) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH)) AND MONTH(appointment_Date) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH))) as lastMonth
                    ')->first();

                    $monthly = (clone $base)
                        ->selectRaw("DATE_FORMAT(appointment_Date, '%Y-%m') as month, COUNT(*) as total")
                        ->whereNotNull('appointment_Date')
                        ->where('appointment_Date', '>=', now()->subMonths(5)->startOfMonth())
                        ->groupBy('month')->orderBy('month')->get();

                    $topSpec = (clone $base)
                        ->selectRaw('customer_spesiality, COUNT(*) as cnt')
                        ->whereNotNull('customer_spesiality')->where('customer_spesiality', '<>', '')
                        ->groupBy('customer_spesiality')->orderByDesc('cnt')->limit(3)->get();

                    return [
                        'total'          => (int) ($kpi->total ?? 0),
                        'avgDur'         => (int) ($kpi->avgDur ?? 0),
                        'lastDate'       => $kpi->lastDate,
                        'thisMonth'      => (int) ($kpi->thisMonth ?? 0),
                        'lastMonth'      => (int) ($kpi->lastMonth ?? 0),
                        'doctorVisits'   => (int) ($kpi->doctorVisits ?? 0),
                        'pharmacyVisits' => (int) ($kpi->pharmacyVisits ?? 0),
                        'monthly'        => $monthly,
                        'topSpec'        => $topSpec,
                        'employeeId'     => $employeeId,
                    ];
                }
            );
        } catch (\Exception $e) {
            // Nobel DB недоступна
            return null;
        }
    }

    private function getKmpStats(Employee $employee): ?array
    {
        $kmpNames = $employee->kmp_employee_names;
        if (empty($kmpNames)) {
            return null;
        }

        try {
            sort($kmpNames);
            $employeeId = $employee->id;

            return \Illuminate\Support\Facades\Cache::remember(
                'employee_kmp_stats_' . md5(implode('|', $kmpNames)),
                3600,
                function () use ($kmpNames, $employeeId) {
                    $currentYear = (int) now()->year;
                    // whereIn по всем привязанным именам КМП сотрудника — агрегация
                    // покрывает случай, когда КМП завёл новую учётку при повторном найме.
                    $base = Kmp::whereIn('Медпредставитель', $kmpNames)
                        ->where('Статус заказа', 'Доставлено')
                        ->where('Год', $currentYear);

                    $kpi = (clone $base)->selectRaw('
                        ROUND(SUM(`Amount_disc`)) as totalAmount,
                        ROUND(SUM(CASE WHEN MONTH(`Дата`) = MONTH(NOW()) THEN `Amount_disc` END)) as thisMonth,
                        ROUND(SUM(CASE WHEN MONTH(`Дата`) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH)) THEN `Amount_disc` END)) as lastMonth
                    ')->first();

                    $monthly = (clone $base)
                        ->selectRaw("DATE_FORMAT(`Дата`, '%Y-%m') as month, ROUND(SUM(`Amount_disc`)) as amount")
                        ->whereNotNull('Дата')
                        ->where('Дата', '>=', now()->subMonths(5)->startOfMonth())
                        ->groupBy('month')->orderBy('month')->get();

                    $topBrands = (clone $base)
                        ->selectRaw('`Брэнд` as brand, ROUND(SUM(`Amount_disc`)) as amount')
                        ->whereNotNull('Брэнд')->where('Брэнд', '<>', '')
                        ->groupBy('Брэнд')->orderByDesc('amount')->limit(4)->get();

                    return [
                        'totalAmount' => (int) ($kpi->totalAmount ?? 0),
                        'thisMonth'   => (int) ($kpi->thisMonth ?? 0),
                        'lastMonth'   => (int) ($kpi->lastMonth ?? 0),
                        'monthly'     => $monthly,
                        'topBrands'   => $topBrands,
                        'employeeId'  => $employeeId,
                        'year'        => $currentYear,
                    ];
                }
            );
        } catch (\Exception $e) {
            // Nobel DB недоступна
            return null;
        }
    }

    /**
     * Show the form for editing the specified employee.
     *
     * @param Employee $employee
     * @return \Illuminate\View\View
     */
    public function showEditEmployee(Employee $employee)
    {
        return view('create-edit-employee', ['employee' => $employee]);
    }

    /**
     * Update the specified employee.
     *
     * @param EmployeeUpdateRequest $request
     * @param Employee $employee
     * @return \Illuminate\Http\RedirectResponse
     */
    public function actuallyEditEmployee(EmployeeUpdateRequest $request, Employee $employee)
    {
        $incomingFields = $request->validated();
        $employee->update($incomingFields);

        return redirect()->route('employees.show', ['id' => $employee->id])
            ->with('success', 'Employee edited successfully!');
    }

    /**
     * Remove the specified employee.
     *
     * @param Employee $employee
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteEmployee(Employee $employee)
    {
        $fullName = $employee->full_name;
        $admin    = auth()->user();

        try {
            $employee->delete();

            // Тестовая обкатка email-отправки: письмо тому же админу, который удалил.
            if ($admin) {
                $admin->notify(new EmployeeDeletedNotification($fullName, $admin->full_name));
            }

            return redirect('/')->with('success', 'Employee deleted successfully!');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23000') {
                return redirect('/')->with('error', 'Cannot delete employee because there are related records.');
            }
            return redirect('/')->with('error', 'An error occurred while deleting the employee.');
        }
    }

    /**
     * Display a listing of employees.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $activeOnly = $request->input('active_only', 1);
        $sort = $request->input('sort', 'event_date');
        $order = $request->input('order', 'asc');

        $employees = \Illuminate\Support\Facades\DB::table('employees as e')
            ->join('employee_events as ev', 'ev.employee_id', '=', 'e.id')
            ->where('ev.event_type', 'hired')
            ->whereRaw('ev.id = (
                SELECT ee.id FROM employee_events ee
                WHERE ee.employee_id = ev.employee_id
                ORDER BY ee.event_date DESC
                LIMIT 1
            )')
            ->select('e.*', 'ev.event_type', 'ev.event_date')
            ->orderBy('ev.event_date', 'DESC')
            ->get();

        if ($request->ajax()) {
            return view('components.employee-card', compact('employees', 'sort', 'order'))->render();
        }

        return view('home', compact('employees', 'sort', 'order', 'activeOnly'));
    }

    /**
     * Search for employees.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function searchEmployee(Request $request)
    {
        $query = $request->input('search');
        $sort = $request->input('sort', 'latest_event_date');
        $order = $request->input('order', 'desc');
        $activeOnly = $request->input('active_only', 1);

        $employees = Employee::with([
                'latestEvent',
                'employee_territory' => fn ($q) => $q->orderByDesc('assigned_at'),
            ])
            ->search($query)
            ->when($activeOnly == 1, function ($q) {
                $q->active();
            })
            ->get();

        $employees = $employees->when(
            $order === 'desc',
            fn($c) => $c->sortByDesc(fn($e) =>
                $sort === 'latest_event_date'
                    ? optional($e->latestEvent)->event_date
                    : data_get($e, $sort)
            ),
            fn($c) => $c->sortBy(fn($e) =>
                $sort === 'latest_event_date'
                    ? optional($e->latestEvent)->event_date
                    : data_get($e, $sort)
            )
        )->values();

        $perPage = 50;
        $page = (int) $request->input('page', 1);
        $employees = new \Illuminate\Pagination\LengthAwarePaginator(
            $employees->slice(($page - 1) * $perPage, $perPage)->values(),
            $employees->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        if ($request->ajax()) {
            return response(
                view('components.employee-card', compact('employees', 'sort', 'order'))->render()
            )->header('X-Total-Count', $employees->total());
        }

        return view('home', [
            'employees' => $employees,
            'query' => $query,
            'sort' => $sort,
            'order' => $order,
            'activeOnly' => $activeOnly,
        ]);
    }

    /**
     * Display the user's team.
     *
     * @return \Illuminate\View\View
     */


    public function myTeam(TeamService $teamService)
    {
        // getTeamStructure() уже eager-load'ит всё дерево территорий одним проходом
        // и готовит $ffm->ffmStats/$ffm->preparedRms; groupByDepartment() строит
        // готовую по-департаментную разбивку (включая team-view) из ЭТИХ же данных,
        // без единого дополнительного запроса. Раньше view игнорировал это и сам
        // дёргал ->employeeTerritories() (со скобками — свежий SQL, а не обращение
        // к уже загрученной связи) внутри вложенных циклов, по несколько раз на
        // одну и ту же территорию — N+1, упиравшийся в лимит 30 секунд.
        $ffms = $teamService->getTeamStructure();
        $grouped = $teamService->groupByDepartment($ffms);

        $productTerritories = \App\Models\Territory::where('role', 'Product')
            ->with(['employeeTerritories.employee.latestEvent'])
            ->orderBy('department', 'desc')
            ->orderBy('team')
            ->orderBy('city')
            ->get()
            ->groupBy(fn($t) => $t->department ?? 'Без департамента');

        return view('my-team', compact('grouped', 'productTerritories'));
    }



    /**
     * Update employee credentials.
     *
     * @param EmployeeCredentialsUpdateRequest $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadPhoto(\Illuminate\Http\Request $request, Employee $employee)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        if ($employee->photo_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($employee->photo_path);
        }

        $path = $request->file('photo')->store('employees/photos', 'public');
        $employee->update(['photo_path' => $path]);

        return back()->with('success', 'Фото обновлено.');
    }

    public function updateCredentials(EmployeeCredentialsUpdateRequest $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $validated = $request->validated();

        // Проверяем, есть ли уже такой логин
        $credential = EmployeeCredential::where('employee_id', $employee->id)
            ->where('system', $validated['system'])
            ->first();

        $payload = [
            'user_name' => trim($validated['user_name'] ?? '') ?: '',
            'login' => trim($validated['login'] ?? '') ?: '',
            'password' => trim($validated['password'] ?? '') ?: '',
            'add_password' => trim($validated['add_password'] ?? '') ?: '',
        ];

        if ($credential) {
            // Обновляем существующий логин
            $credential->update($payload);
        } else {
            // Создаём новый
            EmployeeCredential::create([
                'employee_id' => $employee->id,
                'system' => $validated['system'],
            ] + $payload);
        }

        return redirect()->back()->with('success', 'Данные обновлены.');
    }
}
