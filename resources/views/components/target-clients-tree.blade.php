{{-- Дерево Отдел -> Сотрудник (счётчики уникальных клиентов), целиком на клиенте:
     $treeExpr — JS-выражение (в области видимости родительского x-data targetClients()),
     возвращающее массив [{department, client_count, employees:[{employee,manager,client_count}]}].
     openDepts (общий объект в родительском x-data, ключ — название отдела) хранит,
     какие ветки развёрнуты — переключение отдела в фильтре не требует запроса к серверу. --}}
<div style="background:#fff;border:1px solid #f0f0f0;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.05);">
    <template x-if="({{ $treeExpr }}).length === 0">
        <div style="padding:32px;text-align:center;color:#9ca3af;font-size:13px;">Нет данных за этот период</div>
    </template>
    <template x-for="dept in {{ $treeExpr }}" :key="dept.department">
        <div style="border-bottom:1px solid #f0f0f0;">
            <div @click="openDepts[dept.department] = !openDepts[dept.department]"
                 style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;
                        cursor:pointer;font-size:13px;"
                 onmouseover="this.style.background='#fafafa';" onmouseout="this.style.background='none';">
                <div style="display:flex;align-items:center;gap:8px;">
                    <svg class="tc-chevron" :class="{ 'tc-chevron-open': openDepts[dept.department] }"
                         style="width:12px;height:12px;color:#9ca3af;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span style="font-weight:600;color:#111827;" x-text="dept.department"></span>
                </div>
                <span style="font-weight:700;color:#2563eb;" x-text="fmt(dept.client_count)"></span>
            </div>

            <div x-show="openDepts[dept.department]" x-cloak>
                <table style="width:100%;border-collapse:collapse;font-size:12px;">
                    <tbody>
                        <template x-for="emp in dept.employees" :key="emp.employee">
                            <tr style="border-top:1px solid #f0f0f0;background:#f9fafb;">
                                <td style="padding:8px 16px 8px 34px;color:#374151;">
                                    <span x-text="emp.employee"></span>
                                    <template x-if="emp.manager">
                                        <span style="color:#9ca3af;"> — РМ: <span x-text="emp.manager"></span></span>
                                    </template>
                                </td>
                                <td style="padding:8px 16px;text-align:right;font-weight:600;color:#16a34a;white-space:nowrap;" x-text="fmt(emp.client_count)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </template>
</div>
