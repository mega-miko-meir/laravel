<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tablet extends Model
{
    protected $fillable =  [
        'model',
        'invent_number',
        'serial_number',
        'imei',
        'beeline_number',
        'beeline_number_status',
        'status',
        'old_employee_id',
        'employee_id',
        'pdf_path',
        'unassign_pdf'
    ];

    public function employee(){
        return $this->belongsTo(Employee::class);
    }

    public function oldEmployee(){
        return $this->belongsTo(Employee::class, 'old_employee_id');
    }

    public function employees(){
        return $this->belongsToMany(Employee::class, 'employee_tablet')
                    ->withPivot('assigned_at', 'returned_at', 'confirmed', 'pdf_path', 'unassign_pdf', 'employee_id', 'tablet_id')
                    ->withTimestamps();
    }

    public function currentAssignment(){
        return $this->hasOne(EmployeeTablet::class, 'tablet_id')
            ->orderByDesc('assigned_at'); // Последняя запись
    }

    public function latestAssignment()
    {
        return $this->hasOne(EmployeeTablet::class)
            ->latest('assigned_at');
    }

    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function getCurrentEmployeeAttribute()
    {
        $assignment = $this->latestAssignment()->first();

        if (!$assignment || $assignment->returned_at !== null) {
            return null; // не назначен
        }

        return $assignment->employee; // сотрудник из таблицы employee_tablet
    }


    public function scopeFree($query)
    {
        return $query->whereIn('status', ['active', 'admin'])
            ->where(function ($q) {
                $q->whereHas('employees', function ($q) {
                    $q->whereNotNull('returned_at')
                        ->whereRaw('assigned_at = (
                            SELECT MAX(assigned_at)
                            FROM employee_tablet
                            WHERE employee_tablet.tablet_id = tablets.id
                        )');
                })
                ->orWhereDoesntHave('employees');
            });
    }




    // public function currentAssignment()
    // {
    //     return $this->hasOne(Assignment::class)->latestOfMany();
    // }

}
