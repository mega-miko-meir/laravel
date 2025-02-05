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
        'employee_id'];

    public function employee(){
        return $this->belongsTo(Employee::class);
    }

    public function oldEmployee(){
        return $this->belongsTo(Employee::class, 'old_employee_id');
    }

    public function employees(){
        return $this->belongsToMany(Employee::class, 'employee_tablet')
                    ->withPivot('assigned_at', 'returned_at', 'confirmed', 'pdf_path')
                    ->withTimestamps();
    }
}
