<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Tablet;
use App\Models\Territory;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    /**
     * Единый поиск по сотрудникам/территориям/планшетам для Ctrl+K в шапке.
     * OneKey (врачи/аптеки) намеренно не включён — это Nobel DB, тяжёлые
     * TEXT-запросы без индексов, испортили бы отклик "быстрого" поиска.
     */
    public function search(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['employees' => [], 'territories' => [], 'tablets' => []]);
        }

        $like = '%' . $q . '%';

        $employees = Employee::where('full_name', 'like', $like)
            ->orWhere('email', 'like', $like)
            ->orderBy('full_name')
            ->limit(5)
            ->get(['id', 'full_name', 'position'])
            ->map(fn($e) => [
                'title'    => $e->full_name,
                'subtitle' => $e->position ?? '',
                'url'      => "/employee/{$e->id}",
            ]);

        $territories = Territory::where('territory_name', 'like', $like)
            ->orWhere('city', 'like', $like)
            ->orderBy('territory_name')
            ->limit(5)
            ->get(['id', 'territory_name', 'city'])
            ->map(fn($t) => [
                'title'    => $t->territory_name,
                'subtitle' => $t->city ?? '',
                'url'      => route('territories.show', $t->id),
            ]);

        $tablets = Tablet::where('invent_number', 'like', $like)
            ->orWhere('serial_number', 'like', $like)
            ->orderBy('invent_number')
            ->limit(5)
            ->get(['id', 'invent_number', 'serial_number'])
            ->map(fn($t) => [
                'title'    => $t->invent_number,
                'subtitle' => $t->serial_number ?? '',
                'url'      => route('tablets.show', $t->id),
            ]);

        return response()->json([
            'employees'   => $employees,
            'territories' => $territories,
            'tablets'     => $tablets,
        ]);
    }
}
