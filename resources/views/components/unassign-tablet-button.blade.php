@props(['employee', 'tablet'])

<div x-data="{ open: false }">

    <button @click="open = true"
            style="display:inline-flex;align-items:center;gap:5px;padding:5px 10px;
                   background:#fff;color:#ef4444;border:1px solid #fca5a5;border-radius:7px;
                   font-size:11px;font-weight:600;cursor:pointer;"
            onmouseover="this.style.background='#fef2f2';"
            onmouseout="this.style.background='#fff';">
        <svg style="width:12px;height:12px;flex-shrink:0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
        </svg>
        Отвязать
    </button>

    {{-- Модальное окно (Alpine) --}}
    <div x-show="open" x-cloak
         style="position:fixed;inset:0;z-index:60;display:flex;align-items:center;justify-content:center;
                background:rgba(0,0,0,0.45);">
        <div @click.outside="open = false"
             style="background:#fff;border-radius:14px;box-shadow:0 20px 60px rgba(0,0,0,.2);
                    padding:24px;width:100%;max-width:360px;margin:0 16px;">

            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                <div style="width:36px;height:36px;border-radius:50%;background:#fee2e2;
                            display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg style="width:18px;height:18px;color:#ef4444;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <p style="font-size:14px;font-weight:700;color:#111827;">Отвязка планшета</p>
                    <p style="font-size:12px;color:#6b7280;">Прикрепите PDF и укажите дату</p>
                </div>
            </div>

            <form action="{{ route('unassign-tablet', [$employee->id, $tablet->id]) }}"
                  method="POST" enctype="multipart/form-data">
                @csrf

                <div style="margin-bottom:12px;">
                    <label style="display:block;font-size:11px;font-weight:600;color:#9ca3af;
                                  text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">
                        Акт возврата (PDF)
                    </label>
                    <input type="file" name="unassign_pdf" accept="application/pdf"
                           style="width:100%;font-size:12px;color:#374151;border:1px solid #e5e7eb;
                                  border-radius:8px;padding:6px 8px;box-sizing:border-box;">
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block;font-size:11px;font-weight:600;color:#9ca3af;
                                  text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">
                        Дата возврата
                    </label>
                    <input type="date" name="returned_at" required
                           value="{{ now()->format('Y-m-d') }}"
                           style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:8px;
                                  font-size:13px;outline:none;box-sizing:border-box;">
                </div>

                <div style="display:flex;gap:10px;justify-content:flex-end;">
                    <button type="button" @click="open = false"
                            style="padding:8px 16px;font-size:13px;color:#374151;background:#fff;
                                   border:1px solid #e5e7eb;border-radius:8px;cursor:pointer;font-weight:500;">
                        Отмена
                    </button>
                    <button type="submit"
                            style="padding:8px 16px;font-size:13px;font-weight:600;color:#fff;
                                   background:#ef4444;border:none;border-radius:8px;cursor:pointer;"
                            onmouseover="this.style.background='#dc2626';"
                            onmouseout="this.style.background='#ef4444';">
                        Отвязать
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
