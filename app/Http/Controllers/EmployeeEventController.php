<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\EmployeeEvent;
use App\Services\TabletAssignmentService;
use App\Services\TerritoryAssignmentService;
use Symfony\Contracts\EventDispatcher\Event;

class EmployeeEventController extends Controller
{
    public function addingEvent(Request $request, Employee $employee, TerritoryAssignmentService $territoryAssignmentService, TabletAssignmentService $tabletAssignmentService)
    {
        $request->validate([
            'event_type' => 'required|string',
            'event_date' => 'nullable|date',
        ]);

        $eventDate = $request->event_date ?? now();
        $returned_at = $request->event_date ?? now();


        $latestEvent = $employee->events()->latest('event_date')->first();
        $currentStatus = $latestEvent?->event_type;
        $currentStatusDate = $latestEvent?->event_date;
        // dd($currentStatus, $request->event);
        // Проверяем, изменился ли статус



        if ($currentStatus === $request->event_type && $currentStatusDate === $request->event_date) {
            return back()->with('error', 'Статус сотрудника уже установлен.');
        } elseif(($currentStatus !== $request->event_type && $currentStatusDate === $request->event_date) || $currentStatus === $request->event_type && $currentStatusDate !== $request->event_date){
            $latestEvent->update(['event_type' => $request->event_type, 'event_date' => $eventDate]);
            return back()->with('success', 'Статус сотрудника обновлен.');
        }

        // Обновляем статус и даты в таблице employees
        // $updateData = ['status' => $request->event];

        if ($currentStatus === 'new' && $request->event_type === 'hired' ?? $latestEvent) {
            $latestEvent->update(['event_type' => 'hired', 'event_date' => $eventDate]);
            // $updateData['hiring_date'] = $eventDate;
            // $updateData['firing_date'] = null;
        } else
        //( $request->event_type === 'dismissed' || $request->event_type === 'maternity_leave' || $request->event_type === 'long_vacation')
        {
            // $updateData['firing_date'] = $eventDate;

            EmployeeEvent::create([
                'employee_id' => $employee->id,
                'event_type' => $request->event_type,
                'event_date' => $eventDate,
            ]);
        }

        $needUnassign = in_array($request->event_type, [
            'dismissed',
            'maternity_leave',
            'change_position',
            'long_vacation'
        ]);

        if($needUnassign){
            $territory = $employee->employee_territory()->latest('assigned_at')->first();

            if($territory){
                $territoryAssignmentService->unassign($employee, $territory, $eventDate);
            }

            $tablet = $employee->employee_tablet()->latest('assigned_at')->first();

            if($tablet){
                $tabletAssignmentService->unassignTablet($employee, $tablet, $returned_at);
            }
        }

        // $employee->update($updateData);
        return back()->with('success', 'Статус сотрудника обновлен.');
    }


    // public function updateStatus(Request $request, Employee $employee){
    //     $request->validate([
    //         'status' => 'required|in:new,active,dismissed,maternity_leave,long_vacation',
    //     ]);

    //     $employee->update([
    //         'status' => $request->status,
    //         'hiring_date' => $request->status === 'active' ? now() : $employee->hiring_date,
    //         'firing_date' => in_array($request->status, ['dismissed', 'maternity_leave']) ? now() : null
    //         // 'firing_date' => in_array($request->status, ['dismissed', 'maternity_leave']) ? null,
    //     ]);

    //     return redirect()->back()->with('success', 'Статус сотрудника успешно обновлен.');
    // }


    public function destroy($id){
        EmployeeEvent::findOrFail($id)->delete();

        return back()->with('success', 'Событие удалено');
    }
}
