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

    public function getPasswordAttribute($value)
    {
        if (empty($value)) return $value;
        try {
            return decrypt($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = $value ? encrypt($value) : $value;
    }

    public function getAddPasswordAttribute($value)
    {
        if (empty($value)) return $value;
        try {
            return decrypt($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function setAddPasswordAttribute($value)
    {
        $this->attributes['add_password'] = $value ? encrypt($value) : $value;
    }
}

