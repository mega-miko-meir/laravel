@extends('layout')

@section('content')
<div style="max-width:1100px;margin:0 auto;">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 style="font-size:22px;font-weight:700;color:#1e3a8a;margin:0;">Привязка сотрудников KMP</h1>
            <p style="font-size:13px;color:#64748b;margin:4px 0 0;">Медпредставители из KMP (kmp.Медпредставитель) → сотрудники системы</p>
        </div>
        <form method="POST" action="{{ route('admin.kmp-mapping.auto') }}">
            @csrf
            <button type="submit"
                style="background:#1d4ed8;color:#fff;border:none;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:8px;">
                <svg style="width:15px;height:15px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Автоматически привязать
            </button>
        </form>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div style="background:#dcfce7;border:1px solid #86efac;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#166534;font-size:13px;">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#991b1b;font-size:13px;">
            @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
    @endif
    @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#991b1b;font-size:13px;">
            {{ session('error') }}
        </div>
    @endif

    {{-- Stats --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
        <div style="background:#fff;border-radius:12px;padding:20px;border:1px solid #e2e8f0;text-align:center;">
            <div style="font-size:28px;font-weight:700;color:#1e3a8a;">{{ $kmpTotal }}</div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;">МП в KMP</div>
        </div>
        <div style="background:#fff;border-radius:12px;padding:20px;border:1px solid #e2e8f0;text-align:center;">
            <div style="font-size:28px;font-weight:700;color:#16a34a;">{{ $mapped }}</div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;">Привязано</div>
        </div>
        <div style="background:#fff;border-radius:12px;padding:20px;border:1px solid #e2e8f0;text-align:center;">
            <div style="font-size:28px;font-weight:700;color:#dc2626;">{{ $kmpTotal - $mapped }}</div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;">Не привязано</div>
        </div>
    </div>

    {{-- Employee picker script --}}
    <script>
    const KMP_SYS_EMPLOYEES = @json($sysEmployees->map(fn($e) => [
        'id'    => $e->id,
        'label' => $e->full_name . ($e->position ? ' (' . $e->position . ')' : ''),
    ])->values());

    function kmpEmpPicker(initId, initLabel) {
        return {
            selected: initId,
            query:    initLabel,
            open:     false,
            pos:      { top: 0, left: 0, width: 280 },
            get filtered() {
                const q = this.query.trim().toLowerCase();
                if (!q) return KMP_SYS_EMPLOYEES.slice(0, 80);
                return KMP_SYS_EMPLOYEES.filter(e => e.label.toLowerCase().includes(q)).slice(0, 80);
            },
            openPicker(event) {
                const rect = event.currentTarget.getBoundingClientRect();
                this.pos = { top: rect.bottom + 2, left: rect.left, width: rect.width };
                this.open = true;
                window.addEventListener('scroll', () => { this.open = false; }, { once: true });
            },
            choose(emp) {
                this.selected = emp.id;
                this.query    = emp.label;
                this.open     = false;
            },
            clear() {
                this.selected = null;
                this.query    = '';
            },
        };
    }
    </script>

    {{-- Table --}}
    <div x-data="{ tab: 'unmapped', search: '' }">

        <style>
            [x-cloak] { display:none !important; }
            .kmp-tab { border:1px solid #d1d5db; border-radius:6px; padding:6px 14px; font-size:13px; font-weight:500; cursor:pointer; background:#fff; color:#374151; }
            .kmp-tab-blue  { background:#1d4ed8; color:#fff; border-color:#1d4ed8; }
            .kmp-tab-red   { background:#dc2626; color:#fff; border-color:#dc2626; }
            .kmp-tab-green { background:#16a34a; color:#fff; border-color:#16a34a; }
        </style>

        <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;align-items:center;">
            <button @click="tab='all'" class="kmp-tab" :class="tab==='all' ? 'kmp-tab-blue' : ''">
                Все ({{ $kmpTotal }})
            </button>
            <button @click="tab='unmapped'" class="kmp-tab kmp-tab-red" :class="tab==='unmapped' ? 'kmp-tab-red' : ''">
                Не привязано ({{ $kmpTotal - $mapped }})
            </button>
            <button @click="tab='mapped'" class="kmp-tab" :class="tab==='mapped' ? 'kmp-tab-green' : ''">
                Привязано ({{ $mapped }})
            </button>
            <input x-model="search" type="text" placeholder="Поиск по имени..."
                style="margin-left:auto;border:1px solid #d1d5db;border-radius:6px;padding:6px 12px;font-size:13px;outline:none;width:220px;">
        </div>

        <div style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                        <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;width:36px;"></th>
                        <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;">МП в KMP</th>
                        <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;">Привязан к сотруднику системы</th>
                        <th style="padding:10px 16px;text-align:left;font-weight:600;color:#374151;width:90px;"></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($kmpEmployees as $kmp)
                    @php
                        $linked   = $linkedByName->get($kmp->name);
                        $isMapped = !is_null($linked);
                        $nameLower = mb_strtolower(trim($kmp->name));
                    @endphp
                    <tr
                        x-show="
                            (tab==='all'
                             || (tab==='mapped'   && {{ $isMapped  ? 'true' : 'false' }})
                             || (tab==='unmapped' && {{ !$isMapped ? 'true' : 'false' }}))
                            && (search==='' || '{{ addslashes($nameLower) }}'.includes(search.toLowerCase()))
                        "
                        style="border-bottom:1px solid #f1f5f9;"
                        onmouseover="this.style.background='#f8fafc'"
                        onmouseout="this.style.background=''"
                    >
                        <td style="padding:10px 16px;">
                            @if($isMapped)
                                <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#16a34a;"></span>
                            @else
                                <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#dc2626;"></span>
                            @endif
                        </td>
                        <td style="padding:10px 16px;">
                            <div style="font-weight:500;color:#1e293b;">{{ $kmp->name }}</div>
                        </td>
                        <td style="padding:10px 16px;">
                            @php $initLabel = $linked ? ($linked->full_name . ($linked->position ? ' ('.$linked->position.')' : '')) : '' @endphp
                            <form method="POST" action="{{ route('admin.kmp-mapping.link') }}"
                                  style="display:flex;gap:6px;align-items:center;">
                                @csrf
                                <input type="hidden" name="kmp_name" value="{{ $kmp->name }}">

                                <div x-data="kmpEmpPicker({{ $linked ? $linked->id : 'null' }}, @js($initLabel))"
                                     @click.outside="open=false"
                                     style="position:relative;flex:1;max-width:280px;">
                                    <div style="position:relative;">
                                        <input type="text"
                                               x-model="query"
                                               @focus="openPicker($event)"
                                               @input="open=true"
                                               @keydown.escape="open=false"
                                               autocomplete="off"
                                               placeholder="Поиск сотрудника..."
                                               style="width:100%;box-sizing:border-box;border:1px solid #d1d5db;border-radius:6px;padding:5px 22px 5px 8px;font-size:12px;color:#374151;outline:none;">
                                        <span x-show="query" @click="clear()"
                                              style="position:absolute;right:6px;top:50%;transform:translateY(-50%);cursor:pointer;color:#94a3b8;font-size:15px;line-height:1;user-select:none;">×</span>
                                    </div>
                                    <input type="hidden" name="employee_id" x-ref="hiddenEmpId"
                                           x-effect="$refs.hiddenEmpId.value = selected ?? ''">

                                    <div x-show="open" x-cloak
                                         :style="`position:fixed;top:${pos.top}px;left:${pos.left}px;width:${pos.width}px;z-index:9999;background:#fff;border:1px solid #d1d5db;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.1);max-height:220px;overflow-y:auto;`">
                                        <div @click="clear()"
                                             style="padding:6px 10px;font-size:12px;color:#94a3b8;cursor:pointer;border-bottom:1px solid #f1f5f9;"
                                             onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                                            — не привязан —
                                        </div>
                                        <template x-for="emp in filtered" :key="emp.id">
                                            <div @click="choose(emp)"
                                                 :style="emp.id===selected ? 'background:#eff6ff;font-weight:500;' : ''"
                                                 style="padding:6px 10px;font-size:12px;color:#1e293b;cursor:pointer;"
                                                 onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                                                <span x-text="emp.label"></span>
                                            </div>
                                        </template>
                                        <div x-show="filtered.length===0"
                                             style="padding:8px 10px;font-size:12px;color:#94a3b8;">Не найдено</div>
                                    </div>
                                </div>

                                <button type="submit"
                                    style="white-space:nowrap;background:#e0e7ff;color:#3730a3;border:none;border-radius:6px;padding:5px 10px;font-size:11px;font-weight:600;cursor:pointer;flex-shrink:0;">
                                    Сохранить
                                </button>
                            </form>
                        </td>
                        <td style="padding:10px 16px;">
                            @if($isMapped)
                                <span style="font-size:11px;color:#16a34a;font-weight:500;">{{ $linked->position ?? '' }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

    </div>

</div>
@endsection
