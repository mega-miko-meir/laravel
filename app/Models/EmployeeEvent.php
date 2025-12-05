<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeEvent extends Model
{
    protected $fillable = ['employee_id', 'event_type', 'event_date'];

    protected $attributes = [
        'event_date' => null, // Не нужно ставить now() здесь, лучше проверять в контроллере
    ];

    public function employee(){
        return $this->belongsTo(Employee::class);
    }
}

