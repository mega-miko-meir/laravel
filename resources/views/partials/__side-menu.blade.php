<div class="w-64 bg-blue-900 text-white h-screen p-5 fixed left-0 top-14 shadow-lg">
    <h2 class="text-2xl font-bold mb-6">Меню</h2>
    <ul>
        @can('admin')
            <li class="mb-4">
                <a href="/users" class="transition duration-200
                {{request()->is('users') ? 'text-yellow-300 font-bold' : 'hover:text-gray-300'}}">
                Пользователи
            </a>
        </li>
        @endcan
        <li class="mb-4">
            <a href="/dashboard" class="transition duration-200
            {{request()->is('dashboard') ? 'text-yellow-300 font-bold' : 'hover:text-gray-300'}}">
                Дашборд
            </a>
        </li>
        <li class="mb-4">
            <a href="/" id="employees-link" class=" transition duration-200
                {{request()->is('/') ? 'text-yellow-300 font-bold' : 'hover:text-gray-300'}}">
                Сотрудники
            </a>
        </li>

        <li class="mb-4">
            <a href="/territories" class="transition duration-200
            {{request()->is('territories') ? 'text-yellow-300 font-bold' : 'hover:text-gray-300'}} ">
                Территории
            </a>
        </li>
        <li class="mb-4">
            <a href="/tablets" class="transition duration-200
            {{request()->is('tablets') ? 'text-yellow-300 font-bold' : 'hover:text-gray-300'}}">
                Планшеты
            </a>
        </li>
        <li class="mb-4">
            <a href="{{ route('employees.my-team') }}" class="transition duration-200
            {{request()->is('my-team') ? 'text-yellow-300 font-bold' : 'hover:text-gray-300'}}">
                Команда
            </a>
        </li>
        {{-- <div class="mb-6">
            <a href="{{ route('employees.my-team') }}"
            class="inline-flex items-center gap-2 px-2 py-1
                    bg-white border border-gray-200 rounded-xl
                    hover:bg-blue-50 hover:border-blue-400 transition">

                <span class="font-semibold text-gray-700">👥 Моя команда</span>
            </a>
        </div> --}}
    </ul>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const link = document.getElementById('employees-link');
    if (!link) return;

    let activeOnly = localStorage.getItem('active_only');

    // если в LS ничего нет — по умолчанию 1
    activeOnly = activeOnly === null ? 1 : Number(activeOnly);

    link.href = `/?active_only=${activeOnly}`;
});
</script>


