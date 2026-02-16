@props(['employee', 'availableTablets', 'tabletHistories', 'lastTablet'])


<div class="space-y-6">
    <div class="bg-white p-6 rounded-lg shadow-md">
        {{-- <h2 class="text-xl font-semibold text-gray-800">Tablet Assignment</h2> --}}
        @php
            // dd($lastTablet, $tabletHistories->last())
            $tablet = $tabletHistories->first();
        @endphp
        {{-- @if($employee->tablets->isNotEmpty()) --}}
        {{-- @if ($lastTablet && is_null(optional($lastTablet->pivot)->returned_at)) --}}
        @if ($tablet && is_null($tablet->returned_at))
            <div class="mt-4">
                <p class="text-lg text-gray-800 font-medium mb-2">Планшет:</p>
                <ul class="space-y-3">
                    <li class="flex flex-col gap-2 text-sm text-gray-700 p-3 rounded-lg shadow-sm">

                        <!-- Верхняя строка: информация о планшете -->
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div>
                                <a href="{{ route('tablets.show', $lastTablet->id) }}" class="text-blue-600 hover:underline font-medium">
                                    {{ $lastTablet->invent_number }} - {{ $lastTablet->serial_number}}
                                </a><span>- {{ \Carbon\Carbon::parse($lastTablet->latestAssignment->assigned_at)->format('d.m.Y') }}</span>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <!-- Кнопки печати -->
                                <form action="/print-act/{{$employee->id}}/{{$tablet->tablet_id}}" method="POST">
                                    @csrf
                                    <button class="bg-blue-400 hover:bg-blue-500 text-white text-xs font-semibold py-1 px-2 rounded transition-all">
                                        🖨️ Print
                                    </button>
                                </form>

                                <form action="/print-act2/{{$employee->id}}/{{$tablet->tablet_id}}" method="POST">
                                    @csrf
                                    <button class="bg-blue-400 hover:bg-blue-500 text-white text-xs font-semibold py-1 px-2 rounded transition-all">
                                        🖨️ Print2
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Нижняя строка: действия -->
                        <div class="flex flex-wrap items-center gap-2">
                            <x-pdf-upload-form :employee="$employee" :tablet="$tablet->tablet"/>

                            <x-unassign-tablet-button :employee="$employee" :tablet="$tablet->tablet"/>

                            @if (!$tablet->confirmed)
                                <form action="{{ route('confirm.tablet', [$employee->id, $tablet->tablet_id]) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="bg-green-400 hover:bg-green-500 text-white text-xs font-semibold py-1 px-2 rounded transition-all">
                                        ✅ Confirm
                                    </button>
                                </form>
                            @else
                                <span class="text-green-600 text-xs font-medium">✔️ Confirmed</span>
                            @endif
                        </div>
                    </li>
                </ul>
            </div>
        @else
            <p class="text-lg text-gray-600">Нет назначенных планшетов</p>

            <form action="/assign-tablet/{{$employee->id}}" method="POST" class="mt-3 space-y-2">
                @csrf
                <label for="tablet" class="block text-sm font-medium text-gray-600">Назначить</label>
                <select id="tablet" name="tablet_id" class="w-full p-2 border rounded-lg text-sm">
                    <option value="">No Tablet</option>
                    @foreach ($availableTablets as $tablet)
                        <option value="{{ $tablet->id }}">
                            {{ $tablet->invent_number }} - {{ $tablet->serial_number }} - {{ $tablet->employees->first()->full_name ?? '' }}
                        </option>
                    @endforeach
                </select>

                <input type="date" name="assigned_at" id="assigned_at" value="{{ now()->format('Y-m-d') }}" class="w-full p-2 border rounded-lg text-sm">

                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-1.5 px-4 rounded text-sm">
                    Назначить
                </button>
            </form>
        @endif


        <!-- Tablet Assign Form -->
        {{-- <form action="/assign-tablet/{{$employee->id}}" method="GET" class="mt-4">
            @csrf
            <label for="tablet_search" class="block text-sm font-medium text-gray-600">Search Tablet</label>
            <input type="text" id="tablet_search" name="search" class="w-full p-3 border rounded-lg mt-2" placeholder="Search by serial or invent number" value="{{ request('search') }}">

            <button type="submit" class="btn-primary mt-2 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Search</button>
        </form> --}}

        <div class="bg-white mt-6">
            <button onclick="toggleHistory()" class="w-full text-left font-semibold text-lg text-gray-700 border-b pb-2 mb-3 flex justify-between items-center">
                История
                <svg id="arrowIcon" class="w-5 h-5 transition-transform transform rotate-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <ul id="historyList" class="text-sm text-gray-600 space-y-2">
                @foreach($tabletHistories as $history)
                    <li class="flex justify-between items-center border-b py-2">
                        <div>
                            {{-- <span class="font-medium text-gray-800">
                                {{ $history->id }}
                            </span> --}}
                            <span class="font-medium text-gray-800">
                                <a href="{{route('tablets.show', $history->tablet->id)}}" class="text-blue-500 hover:underline" >{{ $history->tablet ? $history->tablet->serial_number : 'Неизвестный планшет' }}</a>
                            </span>
                            <span class="text-sm text-gray-500 ml-2">
                                {{ \Carbon\Carbon::parse($history->assigned_at)->format('d.m.Y') }} -
                                {{ $history->returned_at ? \Carbon\Carbon::parse($history->returned_at)->format('d.m.Y') : 'Текущий пользователь' }}
                            </span>
                        </div>
                        <div class="space-x-3">
                            @if($history->pdf_path)
                                <a href="{{ asset('storage/' . $history->pdf_path) }}" target="_blank" class="text-blue-500 hover:underline">PDF1</a>
                            @else
                                <form action="/upload"></form>
                            @endif
                            @if($history->unassign_pdf)
                                <a href="{{ asset('storage/' . $history->unassign_pdf) }}" target="_blank" class="text-blue-500 hover:underline">PDF2</a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

    </div>


</div>

<script>
    function toggleHistory() {
        let list = document.getElementById("historyList");
        let arrow = document.getElementById("arrowIcon");

        list.classList.toggle("hidden");
        arrow.classList.toggle("rotate-180");
    }
</script>




<script>
    document.getElementById('toggle-bricks-btn').addEventListener('click', function () {
        const bricksList = document.getElementById('bricks-list');
        bricksList.classList.toggle('hidden'); // Показываем или скрываем список
    });
</script>
