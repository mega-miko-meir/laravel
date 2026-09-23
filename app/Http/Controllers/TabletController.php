<?php

namespace App\Http\Controllers;

use App\Http\Requests\TabletStoreRequest;
use App\Http\Requests\TabletUpdateDateRequest;
use App\Http\Requests\TabletUpdatePdfRequest;
use App\Http\Requests\TabletUpdateRequest;
use App\Models\Tablet;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\EmployeeTablet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\TabletExportService;
use App\Services\AvailableResourcesService;

class TabletController extends Controller
{
    public function __construct(private AvailableResourcesService $available) {}

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Сотрудники которые могут быть ответственными:
     * активны (hired/return_from_leave) и роль территории не Rep и не Product.
     */
    private function getResponsibles()
    {
        return Employee::whereHas('latestEvent', function ($q) {
                $q->whereIn('event_type', ['hired', 'return_from_leave']);
            })
            ->whereHas('employeeTerritoryRecords', function ($q) {
                $q->whereNull('unassigned_at')
                ->whereHas('territory', function ($q2) {
                    $q2->whereNotIn('role', ['Rep', 'Product']);
                });
            })
            ->orderBy('full_name')
            ->get();
    }

    // -------------------------------------------------------------------------
    // CRUD
    // -------------------------------------------------------------------------

    public function createTabletForm()
    {
        $responsibles = $this->getResponsibles();

        return view('create-edit-tablet', compact('responsibles'));
    }

    public function editTabletForm(Tablet $tablet)
    {
        $responsibles = $this->getResponsibles();

        return view('create-edit-tablet', compact('tablet', 'responsibles'));
    }

    public function createTablet(TabletStoreRequest $request)
    {
        $incomingFields = $request->validated();

        $incomingTablet = Tablet::where('serial_number', $incomingFields['serial_number'])->first();

        if ($incomingTablet) {
            return redirect()->back()->with('error', 'Такой iPad уже существует');
        }

        $tablet = Tablet::create($incomingFields);

        return redirect()->route('tablets.show', ['tablet' => $tablet])
            ->with('success', 'Планшет добавлен успешно!');
    }

    public function editTablet(TabletUpdateRequest $request, Tablet $tablet)
    {
        $incomingFields = $request->validated();

        $tablet->update($incomingFields);

        return redirect()->route('tablets.show', ['tablet' => $tablet])
            ->with('success', 'Данные успешно обновлены!');
    }

    // -------------------------------------------------------------------------
    // Search & show
    // -------------------------------------------------------------------------

    private const SORTABLE_COLUMNS = ['invent_number', 'serial_number', 'employee', 'assigned_at'];
    private const SORTABLE_FREE_COLUMNS = ['invent_number', 'serial_number', 'employee', 'responsible', 'city', 'returned_at'];

    public function searchTablet(Request $request)
    {
        $query      = $request->input('search');
        $activeOnly = $request->boolean('active_only');

        // По умолчанию — как раньше: дата привязки, сначала новые. Для остальных
        // колонок первый клик по заголовку логичнее делать по возрастанию (А-Я).
        $sort = in_array($request->input('sort'), self::SORTABLE_COLUMNS) ? $request->input('sort') : 'assigned_at';
        $defaultDir = $sort === 'assigned_at' ? 'desc' : 'asc';
        $dir = in_array($request->input('dir'), ['asc', 'desc']) ? $request->input('dir') : $defaultDir;

        $tablets = Tablet::query()
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {

                    // 1. Приводим к строке, только если это не массив
                    $searchString = is_array($query) ? '' : strtolower($query);

                    // 2. Проверяем условия
                    if ($searchString === 'damaged' || (is_array($query) && in_array('damaged', $query))) {
                        $sub->whereIn('status', ['damaged', 'lost']);
                    } else {
                        // Если это массив (и не damaged), ищем точное совпадение по статусам
                        if (is_array($query)) {
                            $sub->whereIn('status', $query);
                        } else {
                            // Стандартный текстовый поиск
                            $sub->where('serial_number', 'like', "%$query%")
                                ->orWhere('invent_number', 'like', "%$query%")
                                ->orWhere('status', 'like', "%$query%")
                                ->orWhere('model', 'like', "%$query%")
                                ->orWhere('beeline_number', 'like', "%$query%")
                                ->orWhereHas('employees', function ($emp) use ($query) {
                                    $emp->where('full_name', 'like', "%$query%");
                                });
                        }
                    }
                });
            })
            ->with([
                'latestAssignment.employee',
                'currentAssignment',
                'responsible.employee_territory' => fn ($q) => $q->orderByDesc('assigned_at'),
            ])
            ->get();

        $sortKey = match ($sort) {
            'invent_number' => fn ($t) => $t->invent_number,
            'serial_number' => fn ($t) => $t->serial_number,
            'employee'      => fn ($t) => $t->current_employee?->full_name ?? '',
            'assigned_at'   => fn ($t) => optional($t->latestAssignment)->assigned_at,
        };
        $tablets = ($dir === 'asc' ? $tablets->sortBy($sortKey) : $tablets->sortByDesc($sortKey))->values();

        $perPage = 50;
        $page = (int) $request->input('page', 1);
        $tablets = new \Illuminate\Pagination\LengthAwarePaginator(
            $tablets->slice(($page - 1) * $perPage, $perPage)->values(),
            $tablets->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $freeSort = in_array($request->input('free_sort'), self::SORTABLE_FREE_COLUMNS)
            ? $request->input('free_sort') : 'invent_number';
        $freeDir = $request->input('free_dir') === 'desc' ? 'desc' : 'asc';

        $freeTablets = Tablet::free()
            ->with([
                'latestAssignment.employee',
                'currentAssignment',
                'responsible.employee_territory' => fn ($q) => $q->orderByDesc('assigned_at'),
            ])
            ->get();

        $freeSortKey = match ($freeSort) {
            'invent_number' => fn ($t) => $t->invent_number,
            'serial_number' => fn ($t) => $t->serial_number,
            'employee'      => fn ($t) => $t->latestAssignment?->employee?->sh_name ?? '',
            'responsible'   => fn ($t) => $t->responsible?->sh_name ?? '',
            'city'          => fn ($t) => $t->responsible?->employee_territory->first()?->city ?? '',
            'returned_at'   => fn ($t) => optional($t->latestAssignment)->returned_at,
        };
        $freeTablets = ($freeDir === 'asc' ? $freeTablets->sortBy($freeSortKey) : $freeTablets->sortByDesc($freeSortKey))->values();

        // Клик по заголовку колонки перерисовывает только соответствующую таблицу
        // через AJAX — остальной блок страницы не меняется от сортировки, поэтому
        // незачем ни пересчитывать, ни отдавать его целиком.
        if ($request->ajax() && $request->input('partial') === 'free') {
            return view('components.free-tablets-table', compact('freeTablets', 'freeSort', 'freeDir'));
        }

        if ($request->ajax()) {
            return view('components.tablets-table', compact('tablets', 'sort', 'dir'));
        }

        $availableEmployees = $this->available->getAvailableForTablet();
        $count = $availableEmployees->count();

        $tabletStats = \Illuminate\Support\Facades\Cache::remember('tablets_status_counts', 300, function () {
            return [
                'active'  => Tablet::where('status', 'active')->count(),
                'free'    => Tablet::free()->count(),
                'new'     => Tablet::where('status', 'new')->count(),
                'damaged' => Tablet::whereIn('status', ['damaged', 'lost'])->count(),
                'admin'   => Tablet::whereIn('status', ['admin'])->count(),
            ];
        });

        return view('tablets', compact('tablets', 'query', 'freeTablets', 'availableEmployees', 'count', 'tabletStats', 'sort', 'dir', 'freeSort', 'freeDir'));
    }

    public function exportToExcel(Request $request)
    {
        return app(TabletExportService::class)->exportToExcel($request);
    }

    public function showTablet(Tablet $tablet)
    {
        $tablet->load('responsible');

        $previousUsers = $tablet->employees()
            ->withPivot('assigned_at', 'returned_at', 'pdf_path', 'unassign_pdf', 'id', 'employee_id', 'tablet_id')
            ->orderByDesc('employee_tablet.assigned_at')
            ->get();

        $lastTablet = EmployeeTablet::where('tablet_id', $tablet->id)
            ->whereNull('returned_at')
            ->orderByDesc('assigned_at')
            ->first();

        $availableEmployees = $this->available->getAvailableForTablet();

        return view('show-tablet', compact('tablet', 'previousUsers', 'lastTablet', 'availableEmployees'));
    }

    // -------------------------------------------------------------------------
    // Date & PDF updates
    // -------------------------------------------------------------------------

    public function updateDate(TabletUpdateDateRequest $request, $id)
    {
        $validated = $request->validated();

        DB::table('employee_tablet')
            ->where('id', $id)
            ->update([$validated['field_name'] => $validated['date_value']]);

        return back()->with('success', 'Дата обновлена');
    }

    public function updatePdf(TabletUpdatePdfRequest $request, $id)
    {
        $validated = $request->validated();

        $record = DB::table('employee_tablet')
            ->where('id', $id)
            ->first();

        if (!$record) {
            return back()->with('error', 'Запись не найдена');
        }

        $path = $request->file('pdf_value')
            ->store('employee_tablets', 'public');

        if ($record->{$validated['field_name']}) {
            Storage::disk('public')->delete($record->{$validated['field_name']});
        }

        DB::table('employee_tablet')
            ->where('id', $id)
            ->update([$validated['field_name'] => $path]);

        return back()->with('success', 'PDF обновлен');
    }
}
