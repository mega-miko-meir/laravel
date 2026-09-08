<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeKmpName extends Model
{
    protected $table = 'employee_kmp_names';

    protected $fillable = [
        'employee_id',
        'kmp_employee_name',
        'confirmed',
    ];

    protected $casts = [
        'confirmed' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
