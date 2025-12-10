<div class="w-64 bg-blue-900 text-white h-screen p-5 fixed left-0 top-14 shadow-lg">
    <h2 class="text-2xl font-bold mb-6">Меню</h2>
    <ul>
        <li class="mb-4">
            <a href="/dashboard" class="transition duration-200
            {{request()->is('dashboard') ? 'text-yellow-300 font-bold' : 'hover:text-gray-300'}}">
                Дашборд
            </a>
        </li>
        <li class="mb-4">
            <a href="/" class=" transition duration-200
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
    </ul>
</div>

