<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeTablet extends Model
{
    use HasFactory;

    protected $table = 'employee_tablet';

    protected $fillable = [
        'employee_id',
        'tablet_id',
        'pdf_path',
        'assigned_at',
        'returned_at'
    ];
    protected $dated = [
        'assigned_at',
        'returned_at'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function tablet()
    {
        return $this->belongsTo(Tablet::class);
    }
}
