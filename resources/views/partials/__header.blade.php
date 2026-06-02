@php $unreadCount = auth()->user()?->unreadNotifications()->count() ?? 0; @endphp

<div style="position:fixed;top:0;left:0;right:0;height:56px;z-index:50;
            display:flex;align-items:center;justify-content:space-between;
            padding:0 20px;
            background:linear-gradient(135deg,#1e3a8a 0%,#1d4ed8 60%,#2563eb 100%);
            box-shadow:0 1px 0 rgba(255,255,255,.08),0 2px 8px rgba(0,0,0,.18);">

    {{-- Логотип --}}
    <a href="/dashboard"
       style="display:flex;align-items:center;text-decoration:none;opacity:.95;transition:opacity .15s;"
       onmouseover="this.style.opacity='1';"
       onmouseout="this.style.opacity='.95';">
        <img src="/images/nobel-logo.png" alt="Nobel" style="height:32px;width:auto;">
    </a>

    {{-- Правая часть --}}
    <div style="display:flex;align-items:center;gap:4px;">

        {{-- Уведомления (только admin) --}}
        @can('admin')
            <a href="{{ route('admin.notifications') }}"
               style="position:relative;display:flex;align-items:center;justify-content:center;
                      width:34px;height:34px;border-radius:8px;text-decoration:none;
                      color:rgba(255,255,255,.8);transition:background .15s;"
               title="Уведомления"
               onmouseover="this.style.background='rgba(255,255,255,.12)';"
               onmouseout="this.style.background='none';">
                <svg style="width:18px;height:18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                @if($unreadCount > 0)
                    <span style="position:absolute;top:4px;right:4px;width:8px;height:8px;
                                 background:#f87171;border-radius:50%;border:1.5px solid #1e3a8a;">
                    </span>
                @endif
            </a>
        @endcan

        {{-- Обратная связь --}}
        <button @click="feedbackOpen = true"
                style="display:flex;align-items:center;justify-content:center;
                       width:34px;height:34px;border-radius:8px;background:none;border:none;
                       color:rgba(255,255,255,.8);cursor:pointer;transition:background .15s;"
                title="Обратная связь"
                onmouseover="this.style.background='rgba(255,255,255,.12)';"
                onmouseout="this.style.background='none';">
            <svg style="width:18px;height:18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
        </button>

        {{-- Разделитель --}}
        <div style="width:1px;height:22px;background:rgba(255,255,255,.15);margin:0 6px;"></div>

        {{-- Пользователь --}}
        @if(Auth::check())
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="text-align:right;line-height:1.2;">
                    <p style="font-size:10px;color:rgba(191,219,254,.8);font-weight:500;
                               text-transform:uppercase;letter-spacing:.06em;">
                        {{ Auth::user()->first_name }}
                    </p>
                    <p style="font-size:12px;color:#fff;font-weight:600;">
                        {{ Auth::user()->last_name }}
                    </p>
                </div>

                {{-- Logout --}}
                <form action="/logout" method="POST" style="margin:0;">
                    @csrf
                    <button type="submit"
                            style="display:flex;align-items:center;justify-content:center;
                                   width:30px;height:30px;border-radius:7px;background:rgba(255,255,255,.1);
                                   border:1px solid rgba(255,255,255,.15);cursor:pointer;color:rgba(255,255,255,.85);
                                   transition:all .15s;"
                            title="Выйти"
                            onmouseover="this.style.background='rgba(239,68,68,.25)';this.style.borderColor='rgba(239,68,68,.4)';this.style.color='#fca5a5';"
                            onmouseout="this.style.background='rgba(255,255,255,.1)';this.style.borderColor='rgba(255,255,255,.15)';this.style.color='rgba(255,255,255,.85)';">
                        <svg style="width:15px;height:15px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>

