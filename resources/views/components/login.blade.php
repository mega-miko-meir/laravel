<form action="/login" method="POST">
    @csrf

    @if($errors->any())
        <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:10px;
                    padding:10px 14px; margin-bottom:20px;">
            <p style="color:#dc2626; font-size:13px; margin:0;">
                {{ $errors->first() }}
            </p>
        </div>
    @endif

    {{-- Логин --}}
    <div style="margin-bottom:16px;">
        <label style="display:block; font-size:12px; font-weight:600; color:#374151;
                      text-transform:uppercase; letter-spacing:0.06em; margin-bottom:6px;">
            Логин
        </label>
        <div style="position:relative;">
            <svg style="position:absolute; left:12px; top:50%; transform:translateY(-50%);
                        width:16px; height:16px; color:#9ca3af; pointer-events:none;"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <input name="loginname" type="text" placeholder="Введите логин"
                   value="{{ old('loginname') }}"
                   style="width:100%; padding:11px 12px 11px 38px; border:1.5px solid #e5e7eb;
                          border-radius:10px; font-size:14px; outline:none; box-sizing:border-box;
                          transition:border-color .2s; background:#fafafa;"
                   onfocus="this.style.borderColor='#2563eb'; this.style.background='#fff';"
                   onblur="this.style.borderColor='#e5e7eb'; this.style.background='#fafafa';">
        </div>
    </div>

    {{-- Пароль --}}
    <div style="margin-bottom:24px;">
        <label style="display:block; font-size:12px; font-weight:600; color:#374151;
                      text-transform:uppercase; letter-spacing:0.06em; margin-bottom:6px;">
            Пароль
        </label>
        <div style="position:relative;">
            <svg style="position:absolute; left:12px; top:50%; transform:translateY(-50%);
                        width:16px; height:16px; color:#9ca3af; pointer-events:none;"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <input name="loginpassword" type="password" placeholder="Введите пароль"
                   style="width:100%; padding:11px 12px 11px 38px; border:1.5px solid #e5e7eb;
                          border-radius:10px; font-size:14px; outline:none; box-sizing:border-box;
                          transition:border-color .2s; background:#fafafa;"
                   onfocus="this.style.borderColor='#2563eb'; this.style.background='#fff';"
                   onblur="this.style.borderColor='#e5e7eb'; this.style.background='#fafafa';">
        </div>
    </div>

    {{-- Кнопка --}}
    <button type="submit"
            style="width:100%; padding:12px; background:linear-gradient(135deg, #1e3a8a, #2563eb);
                   color:#fff; border:none; border-radius:10px; font-size:14px; font-weight:600;
                   cursor:pointer; letter-spacing:0.02em; transition:opacity .2s;"
            onmouseover="this.style.opacity='0.9';"
            onmouseout="this.style.opacity='1';">
        Войти
    </button>
</form>
