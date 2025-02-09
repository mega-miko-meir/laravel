<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'full_name',
        'first_name',
        'last_name',
        'birth_date',
        'email',
        'hiring_date',
        'position',
        'status'
        ];

    public function territories(){
        return $this->hasMany(Territory::class, 'employee_id');
    }

    public function employee_territory(){
        return $this->belongsToMany(Territory::class, 'employee_territory')
                    ->withPivot('assigned_at', 'unassigned_at', 'confirmed')
                    ->withTimestamps();
    }

    public function tablets(){
        return $this->hasMany(Tablet::class, 'employee_id');
    }

    public function employee_tablet(){
        return $this->belongsToMany(Tablet::class, 'employee_tablet')
                    ->withPivot('id', 'assigned_at', 'returned_at', 'confirmed', 'pdf_path')
                    ->withTimestamps();
    }

    // public function oldTablets(){
    //     return $this->hasMany(Tablet::class);
    // }
}
