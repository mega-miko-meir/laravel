<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\EmployeeEvent;

class EmployeeEventController extends Controller
{
    public function addingEvent(Request $request, Employee $employee)
    {
        $request->validate([
            'event_type' => 'required|string',
            'event_date' => 'nullable|date',
        ]);

        $eventDate = $request->event_date ?? now();


        $latestEvent = $employee->events()->latest('event_date')->first();
        $currentStatus = $latestEvent ? $latestEvent->event_type : null;
        $currentStatusDate = $latestEvent ? $latestEvent->event_date : null;
        // dd($currentStatus, $request->event);
        // Проверяем, изменился ли статус
        if ($currentStatus === $request->event_type && $currentStatusDate === $request->event_date) {
            return back()->with('error', 'Статус сотрудника уже установлен.');
        } elseif(($currentStatus !== $request->event_type && $currentStatusDate === $request->event_date) || $currentStatus === $request->event_type && $currentStatusDate !== $request->event_date){
            $latestEvent->update(['event_type' => 'hired', 'event_date' => $eventDate]);
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

        // $employee->update($updateData);
        return back()->with('success', 'Статус сотрудника обновлен. ваы ');
    }


    public function updateStatus(Request $request, Employee $employee){
        $request->validate([
            'status' => 'required|in:new,active,dismissed,maternity_leave,long_vacation',
        ]);

        $employee->update([
            'status' => $request->status,
            'hiring_date' => $request->status === 'active' ? now() : $employee->hiring_date,
            'firing_date' => in_array($request->status, ['dismissed', 'maternity_leave']) ? now() : null
            // 'firing_date' => in_array($request->status, ['dismissed', 'maternity_leave']) ? null,
        ]);

        return redirect()->back()->with('success', 'Статус сотрудника успешно обновлен.');
    }
}
