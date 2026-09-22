<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;
    protected $fillable = [
        'full_name',
        'first_name',
        'last_name',
        'birth_date',
        'email',
        'hiring_date',
        'firing_date',
        'position',
        'status',
        'photo_path',
    ];

    /**
     * Все привязанные аккаунты CRM (many-to-one: несколько CRM id → один сотрудник,
     * например при повторном найме КМП/CRM заводит новую учётку на того же человека).
     */
    public function crmIds()
    {
        return $this->hasMany(EmployeeCrmId::class);
    }

    /**
     * Все привязанные аккаунты KMP (many-to-one, та же логика, что и crmIds()).
     */
    public function kmpNames()
    {
        return $this->hasMany(EmployeeKmpName::class);
    }

    /** Плоский массив всех привязанных crm_employee_id этого сотрудника. */
    public function getCrmEmployeeIdsAttribute(): array
    {
        return $this->crmIds->pluck('crm_employee_id')->all();
    }

    /** Плоский массив всех привязанных kmp_employee_name этого сотрудника. */
    public function getKmpEmployeeNamesAttribute(): array
    {
        return $this->kmpNames->pluck('kmp_employee_name')->all();
    }

    public function territories(){
        return $this->hasMany(Territory::class, 'employee_id');
    }

    public function employee_territory(){
        return $this->belongsToMany(Territory::class, 'employee_territory')
                    ->withPivot('assigned_at', 'unassigned_at', 'confirmed')
                    ->withTimestamps();
    }

    public function employeeTerritoryRecords()
    {
        return $this->hasMany(EmployeeTerritory::class, 'employee_id');
    }

    public function getCurrentTerritoryAttribute()
    {
        return $this->employee_territory()
            // ->with('territory') // важно!
            ->latest('assigned_at')
            ->first()?->territory;
    }

    public function getCurrentTeamAttribute()
    {
        return $this->employee_territory()
            ->latest('assigned_at')
            ->value('team');
    }

    public function getCurrentRoleAttribute()
    {
        return $this->employee_territory()
            ->latest('assigned_at')
            ->value('role');
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

        return $assignment?->parent
            ?->employeeTerritories()
            ->latest('assigned_at')
            ->first()
            ?->employee;

    }

    public function getFFMAttribute()
    {
        $assignment =  $this->getCurrentManagerAttribute()?->employee_territory()->latest('assigned_at')
            ->first();

        return $assignment?->parent
            ?->employeeTerritories()
            ->latest('assigned_at')
            ->first()
            ?->employee;

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

    public function latestTerritory(): ?Territory
    {
        return $this->employee_territory()->latest('assigned_at')->first();
    }

    public function latestTablet(): ?Tablet
    {
        return $this->employee_tablet()->orderByDesc('assigned_at')->first();
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

    /**
     * Scope for active employees.
     */
    public function scopeActive($query)
    {
        return $query->whereHas('latestEvent', function ($q) {
            $q->whereIn('event_type', ['hired', 'return_from_leave']);
        });
    }

    /**
     * Тот же поиск, что на странице «Сотрудники» (EmployeeController::searchEmployee):
     * текстовое совпадение по имени/должности/почте/территории, либо, если весь
     * запрос — это "rep"/"rm"/"ffm", поиск по роли на последней территории.
     * Вынесено в scope, чтобы выгрузка в Excel могла применить точно тот же
     * фильтр, что сейчас показан на экране, а не дублировать эту логику.
     */
    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        $normalized = strtolower($term);
        $isRoleSearch = in_array($normalized, ['rm', 'rep', 'ffm']);

        return $query->where(function ($q) use ($term, $normalized, $isRoleSearch) {
            if ($isRoleSearch) {
                $q->whereHas('territories', function ($q2) use ($normalized) {
                    $q2->whereRaw('LOWER(role) = ?', [$normalized]);
                });
                return;
            }

            $q->where('first_name', 'like', "%{$term}%")
                ->orWhere('full_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('position', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhereHas('territories', function ($q2) use ($term) {
                    $q2->where('team', 'like', "{$term}%")
                        ->orWhere('city', 'like', "%{$term}%");
                })
                ->orWhereHas('latestEvent', function ($q3) use ($term) {
                    $q3->where('event_type', 'like', "%{$term}%");
                });
        });
    }

    /**
     * Scope for employees with latest event.
     */
    public function scopeWithLatestEvent($query)
    {
        return $query->with('latestEvent');
    }

    /**
     * Scope for FFM position.
     */
    public function scopeFFM($query)
    {
        return $query->where('position', 'FFM');
    }
}
