<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
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
        'firing_date',
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

    public function getCurrentTeamAttribute()
    {
        return $this->employee_territory()
            ->latest('assigned_at')
            ->value('team');
    }

    public function getCurrentCityAttribute()
    {
        return $this->employee_territory()
            ->latest('assigned_at')
            ->value('city');
    }

    public function getCurrentManagerAttribute()
    {
        $assignment =  $this->employee_territory()
            ->latest('assigned_at')
            ->first();

        return optional($assignment)->parent?->employee?->full_name;
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

    public function setStatus(string $status){
        $validStatuses = ['new', 'active', 'dismissed', 'maternity_leave'];

        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException("Недопустимый статус: {$status}");
        }

        $this->update(['status' => $status]);
    }

    public function events(){
        return $this->hasMany(EmployeeEvent::class);
    }

    public function latestEvent()
    {
        return $this->hasOne(EmployeeEvent::class)->latestOfMany('event_date');
    }

    protected static function boot(){
        parent::boot();

        static::creating(function ($employee) {
            $employee->status = 'new';
        });

        static::updated(function ($employee) {
            if ($employee->tablets()->count() === 0) {
                // Проверяем, есть ли загруженный unassign_pdf
                $hasReturnedTablet = DB::table('employee_tablet')
                    ->where('employee_id', $employee->id)
                    ->whereNotNull('returned_at')
                    ->exists();

                if ($hasReturnedTablet) {
                    $employee->setStatus('dismissed');
                }
            }
        });
    }

    public function credentials()
    {
        return $this->hasMany(EmployeeCredential::class);
    }

}
