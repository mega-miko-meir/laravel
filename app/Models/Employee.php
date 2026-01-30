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

    public function getCurrentTerritoryAttribute()
    {
        return $this->employee_territory()
            ->latest('assigned_at')
            ->value('territory');
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


    // public function getCurrentManagerAttribute()
    // {
    //     $assignment =  $this->employee_territory()
    //         ->latest('assigned_at')
    //         ->first();

    //     return optional($assignment)->parent?->employee?->full_name;
    // }

    public function getShNameAttribute()
    {
        $assignment =  $this;

        $name = optional($assignment)->full_name;

        $shortName = $name
            ? implode(' ', array_slice(explode(' ', $name), 0, 2))
            : null;

        return $shortName;

    }

    public function getShNameShAttribute()
    {
        $assignment =  $this;

        $name = optional($assignment)->full_name;

        if (!$name) {
            return null;
        }

        $parts = preg_split('/\s+/', trim($name));

        $lastName = $parts[0] ?? null;
        $firstName = $parts[1] ?? null;

        return $firstName
            ? $lastName . ' ' . mb_substr($firstName, 0, 1) . '.'
            : $lastName;
    }


    public function getCurrentManagerAttribute()
    {
        $assignment =  $this->employee_territory()
            ->latest('assigned_at')
            ->first();

        return optional($assignment)->parent?->employeeTerritories()->latest('assigned_at')->first()->employee;
    }


    public function getCurrentManagerShNameAttribute()
    {

        // Используем уже готовый метод для получения объекта менеджера
        $name = $this->current_manager?->full_name;

        if (!$name) return null;

        // Разбиваем имя на массив, берем первые 2 элемента и склеиваем обратно
        $parts = explode(' ', $name);
        return implode(' ', array_slice($parts, 0, 2));

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
