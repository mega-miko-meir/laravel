<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
class EmployeeTerritory extends Model
{
    protected $table = 'employee_territory';

    protected $fillable = [
        'employee_id',
        'territory_id',
        'assigned_at',
        'unassigned_at'
    ];

    protected $casts = [
        'confirmed' => 'boolean',
    ];

    protected $dates = [
        'assigned_at',
        'unassigned_at'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function territory()
    {
        return $this->belongsTo(Territory::class);
    }

}
