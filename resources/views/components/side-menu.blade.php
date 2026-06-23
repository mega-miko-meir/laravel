@php
    $isAdminPage = request()->is('admin/*') || request()->is('users') || request()->is('activity');
@endphp

<nav style="height:100%;display:flex;flex-direction:column;padding:56px 0 16px;background:#1e3a8a;color:#fff;overflow-y:auto;">

    @php
        $navLink = function(string $href, string $label, string $icon, bool $active): string {
            $base  = 'display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:8px;font-size:13px;font-weight:500;text-decoration:none;';
            $style = $active ? $base . 'background:#1d4ed8;color:#fff;' : $base . 'color:#bfdbfe;';
            $hover = $active ? '' : 'onmouseover="this.style.background=\'#1e40af\';this.style.color=\'#fff\';" onmouseout="this.style.background=\'transparent\';this.style.color=\'#bfdbfe\';"';
            return "<a href=\"{$href}\" style=\"{$style}\" {$hover}>{$icon}{$label}</a>";
        };

        $icons = [
            'dashboard'  => '<svg style="width:16px;height:16px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
            'employees'  => '<svg style="width:16px;height:16px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
            'team'       => '<svg style="width:16px;height:16px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
            'territory'  => '<svg style="width:16px;height:16px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>',
            'tablet'     => '<svg style="width:16px;height:16px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>',
            'tasks'      => '<svg style="width:16px;height:16px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>',
            'calls'      => '<svg style="width:16px;height:16px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>',
            'clients'    => '<svg style="width:16px;height:16px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
            'kmp'        => '<svg style="width:16px;height:16px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
            'link'       => '<svg style="width:16px;height:16px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>',
            'users'      => '<svg style="width:16px;height:16px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
            'activity'   => '<svg style="width:16px;height:16px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>',
            'leaderboard'=> '<svg style="width:16px;height:16px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>',
            'settings'   => '<svg style="width:15px;height:15px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
        ];
    @endphp

    <ul style="flex:1;display:flex;flex-direction:column;gap:2px;list-style:none;margin:0;padding:0 12px;">

        {{-- ── HR ── --}}
        <li>
            <div style="padding:4px 12px;margin-top:8px;margin-bottom:2px;display:flex;align-items:center;gap:8px;">
                <span style="font-size:10px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#60a5fa;">Система</span>
                <div style="flex:1;height:1px;background:#2563eb;opacity:.5;"></div>
            </div>
        </li>

        <li id="employees-link-wrap">
            {!! $navLink('/', 'Сотрудники', $icons['employees'], request()->is('/')) !!}
        </li>
        <li>
            {!! $navLink(route('employees.my-team'), 'Команда', $icons['team'], request()->is('my-team')) !!}
        </li>
        <li>
            {!! $navLink('/dashboard', 'Дашборд', $icons['dashboard'], request()->is('dashboard')) !!}
        </li>
        <li>
            {!! $navLink('/territories', 'Территории', $icons['territory'], request()->is('territories')) !!}
        </li>
        <li>
            {!! $navLink('/tablets', 'Планшеты', $icons['tablet'], request()->is('tablets')) !!}
        </li>
        @can('editor')
        <li>
            {!! $navLink(route('tasks.index'), 'Задачи', $icons['tasks'], request()->is('tasks*')) !!}
        </li>
        @endcan

        {{-- ── CRM ── --}}
        @can('admin')
        <li>
            <div style="padding:4px 12px;margin-top:16px;margin-bottom:2px;display:flex;align-items:center;gap:8px;">
                <span style="font-size:10px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#60a5fa;">CRM</span>
                <div style="flex:1;height:1px;background:#2563eb;opacity:.5;"></div>
            </div>
        </li>
        <li>
            {!! $navLink(route('calls.index'), 'Визиты', $icons['calls'], request()->is('calls*')) !!}
        </li>
        <li>
            {!! $navLink(route('clients.index'), 'База OneKey', $icons['clients'], request()->is('clients')) !!}
        </li>
        <li>
            {!! $navLink(route('leaderboard.index'), 'Рейтинг МП', $icons['leaderboard'], request()->is('leaderboard*')) !!}
        </li>
        @endcan

        {{-- ── KMP ── --}}
        @can('admin')
        <li>
            <div style="padding:4px 12px;margin-top:16px;margin-bottom:2px;display:flex;align-items:center;gap:8px;">
                <span style="font-size:10px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#60a5fa;">KMP</span>
                <div style="flex:1;height:1px;background:#2563eb;opacity:.5;"></div>
            </div>
        </li>
        <li>
            {!! $navLink(route('kmp.index'), 'Продажи', $icons['kmp'], request()->is('kmp*')) !!}
        </li>
        @endcan

        {{-- ── Настройки (collapsible) ── --}}
        @can('admin')
        <li x-data="{ open: {{ $isAdminPage ? 'true' : 'false' }} }" style="margin-top:16px;">

            {{-- Toggle --}}
            <button @click="open = !open"
                    style="width:100%;display:flex;align-items:center;justify-content:space-between;gap:8px;
                           padding:8px 12px;border-radius:8px;border:none;cursor:pointer;
                           background:transparent;color:#93c5fd;font-size:12px;font-weight:600;
                           letter-spacing:0.08em;text-transform:uppercase;"
                    onmouseover="this.style.background='rgba(255,255,255,.07)'"
                    onmouseout="this.style.background='transparent'">
                <div style="display:flex;align-items:center;gap:8px;">
                    {!! $icons['settings'] !!}
                    Настройки
                </div>
                <svg style="width:12px;height:12px;transition:transform .2s;" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                     :style="open ? 'transform:rotate(180deg)' : ''">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            {{-- Collapsible content --}}
            <ul x-show="open" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                style="list-style:none;margin:2px 0 0;padding:0;display:flex;flex-direction:column;gap:2px;">
                <li>{!! $navLink(route('admin.crm-mapping'), 'Привязка CRM', $icons['link'], request()->is('admin/crm-mapping*')) !!}</li>
                <li>{!! $navLink(route('admin.kmp-mapping'), 'Привязка KMP', $icons['link'], request()->is('admin/kmp-mapping*')) !!}</li>
                <li>{!! $navLink('/users', 'Пользователи', $icons['users'], request()->is('users')) !!}</li>
                <li>{!! $navLink(route('activity.logs'), 'Активность', $icons['activity'], request()->is('activity')) !!}</li>
            </ul>
        </li>
        @endcan

    </ul>

</nav>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const wrap = document.getElementById('employees-link-wrap');
    if (!wrap) return;
    const link = wrap.querySelector('a');
    if (!link) return;
    const activeOnly = localStorage.getItem('active_only') ?? 1;
    link.href = `/?active_only=${Number(activeOnly)}`;
});
</script>
