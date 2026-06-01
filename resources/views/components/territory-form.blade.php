@props([
    'action',
    'method'           => 'POST',
    'territory'        => null,
    'parentTerritories'=> null,
    'role'             => collect(config('constants.roles'))->sort()->reverse()->toArray(),
    'department'       => collect(config('constants.departments'))->sort()->toArray(),
    'cities'           => collect(config('constants.cities'))->sort()->toArray(),
    'teams'            => collect(config('constants.teams'))->sort()->toArray(),
])

@if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 14px;margin-bottom:16px;">
        @foreach($errors->all() as $error)
            <p style="color:#dc2626;font-size:13px;margin:0 0 2px;">{{ $error }}</p>
        @endforeach
    </div>
@endif

<form action="{{ $action }}" method="POST">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;
                box-shadow:0 1px 3px rgba(0,0,0,.05);">

        {{-- Основная информация --}}
        <div style="padding:20px 24px;border-bottom:1px solid #f0f0f0;">
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;
                       color:#9ca3af;margin:0 0 16px;">Основная информация</p>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">

                <div style="grid-column:span 2;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                        Название территории <span style="color:#dc2626;">*</span>
                    </label>
                    <input name="territory_name" type="text"
                           value="{{ old('territory_name', $territory->territory_name ?? '') }}"
                           placeholder="Например: Алматы Север"
                           style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                                  font-size:13px;outline:none;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#2563eb';"
                           onblur="this.style.borderColor='#e5e7eb';">
                </div>

                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                        Код территории
                    </label>
                    <input name="territory" type="text"
                           value="{{ old('territory', $territory->territory ?? '') }}"
                           placeholder="ALM-N-01"
                           style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                                  font-size:13px;outline:none;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#2563eb';"
                           onblur="this.style.borderColor='#e5e7eb';">
                </div>

                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                        Роль
                    </label>
                    <select name="role"
                            style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                                   font-size:13px;outline:none;background:#fff;box-sizing:border-box;color:#374151;"
                            onfocus="this.style.borderColor='#2563eb';"
                            onblur="this.style.borderColor='#e5e7eb';">
                        <option value="">— выберите —</option>
                        @foreach($role as $r)
                            <option value="{{ $r }}" {{ old('role', $territory->role ?? '') === $r ? 'selected' : '' }}>
                                {{ $r }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                        Департамент
                    </label>
                    <select name="department"
                            style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                                   font-size:13px;outline:none;background:#fff;box-sizing:border-box;color:#374151;"
                            onfocus="this.style.borderColor='#2563eb';"
                            onblur="this.style.borderColor='#e5e7eb';">
                        <option value="">— выберите —</option>
                        @foreach($department as $dep)
                            <option value="{{ $dep }}" {{ old('department', $territory->department ?? '') === $dep ? 'selected' : '' }}>
                                {{ $dep }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>

        {{-- Расположение --}}
        <div style="padding:20px 24px;border-bottom:1px solid #f0f0f0;">
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;
                       color:#9ca3af;margin:0 0 16px;">Расположение</p>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">

                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                        Город
                    </label>
                    <select name="city"
                            style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                                   font-size:13px;outline:none;background:#fff;box-sizing:border-box;color:#374151;"
                            onfocus="this.style.borderColor='#2563eb';"
                            onblur="this.style.borderColor='#e5e7eb';">
                        <option value="">— выберите —</option>
                        @foreach($cities as $city)
                            <option value="{{ $city }}" {{ old('city', $territory->city ?? '') === $city ? 'selected' : '' }}>
                                {{ $city }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                        Группа
                    </label>
                    <select name="team"
                            style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                                   font-size:13px;outline:none;background:#fff;box-sizing:border-box;color:#374151;"
                            onfocus="this.style.borderColor='#2563eb';"
                            onblur="this.style.borderColor='#e5e7eb';">
                        <option value="">— без группы —</option>
                        @foreach($teams as $team)
                            <option value="{{ $team }}" {{ old('team', $territory->team ?? '') === $team ? 'selected' : '' }}>
                                {{ $team }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>

        {{-- Родительская территория --}}
        <div style="padding:20px 24px;">
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;
                       color:#9ca3af;margin:0 0 16px;">Иерархия</p>

            <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">
                    Родительская территория
                </label>
                <select name="parent_territory_id"
                        style="width:100%;padding:9px 12px;border:1.5px solid #e5e7eb;border-radius:8px;
                               font-size:13px;outline:none;background:#fff;box-sizing:border-box;color:#374151;"
                        onfocus="this.style.borderColor='#2563eb';"
                        onblur="this.style.borderColor='#e5e7eb';">
                    <option value="">— нет родительской —</option>
                    @foreach($parentTerritories as $pt)
                        <option value="{{ $pt->id }}"
                            {{ old('parent_territory_id', $territory->parent_territory_id ?? '') == $pt->id ? 'selected' : '' }}>
                            {{ $pt->territory_name }}
                            @if($pt->employee)
                                — {{ $pt->employee->first_name }} {{ $pt->employee->last_name }}
                            @endif
                        </option>
                    @endforeach
                </select>
                <p style="font-size:11px;color:#9ca3af;margin:4px 0 0;">
                    Оставьте пустым для территорий верхнего уровня (RM, FFM)
                </p>
            </div>
        </div>

    </div>

    {{-- Кнопки --}}
    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px;">
        <a href="/territories"
           style="padding:9px 20px;background:#fff;color:#374151;border:1px solid #e5e7eb;
                  border-radius:8px;font-size:13px;font-weight:500;text-decoration:none;"
           onmouseover="this.style.background='#f9fafb';"
           onmouseout="this.style.background='#fff';">
            Отмена
        </a>
        <button type="submit"
                style="padding:9px 20px;background:#2563eb;color:#fff;border:none;
                       border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;"
                onmouseover="this.style.background='#1d4ed8';"
                onmouseout="this.style.background='#2563eb';">
            {{ $territory ? 'Сохранить изменения' : 'Создать территорию' }}
        </button>
    </div>

</form>
