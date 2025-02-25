<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeCredential extends Model
{
    protected $fillable = ['employee_id', 'system', 'user_name', 'login', 'password', 'add_password'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}

