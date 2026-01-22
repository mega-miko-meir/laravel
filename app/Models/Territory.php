<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Territory extends Model
{
    protected $fillable = [
        'territory',
        'territory_name',
        'department',
        'team',
        'role',
        'manager_id',
        'parent_id',
        'city',
        'old_employee_id',
        'employee_id',
        'parent_territory_id'
    ];

    public function employee(){
        return $this->belongsTo(Employee::class, 'employee_id');
    }

   public function employeeTerritories()
    {
        return $this->hasMany(EmployeeTerritory::class);
    }

    public function oldEmployee(){
        return $this->belongsTo(Employee::class, 'old_employee_id');
    }

    public function employees(){
        return $this->belongsToMany(Employee::class, 'employee_territory')
                    ->withPivot('assigned_at', 'unassigned_at', 'confirmed')
                    ->withTimestamps();
    }

    public function bricks(){
        return $this->belongsToMany(Brick::class, 'brick_territory'); // Связь many-to-many с моделью Brick
    }

    public function parent()
    {
        return $this->belongsTo(Territory::class, 'parent_territory_id');
    }

    public function children()
    {
        return $this->hasMany(Territory::class, 'parent_territory_id');
    }

}
