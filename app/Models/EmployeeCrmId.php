<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeCrmId extends Model
{
    protected $table = 'employee_crm_ids';

    protected $fillable = [
        'employee_id',
        'crm_employee_id',
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
