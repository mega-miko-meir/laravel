@props(['employee', 'availableTablets', 'tabletHistories', 'lastTablet'])

<div class="mt-8 bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-xl font-semibold text-gray-800">Tablet Assignment</h2>

    {{-- @if($employee->tablets->isNotEmpty()) --}}
    @if ($lastTablet && is_null(optional($lastTablet->pivot)->unassigned_at))
        <div class="mt-4">
            <p class="text-lg text-gray-600">
                <span class="font-medium text-gray-800">Tablets:</span>
                <ul class="space-y-4">
                    {{-- @foreach($employee->tablets as $tablet) --}}
                        <li class="flex items-center justify-between text-sm text-gray-600 py-2 border-b border-gray-200">
                            <a href="{{route('tablets.show', $lastTablet->id)}}" class="text-blue-500 hover:underline">
                                {{ $lastTablet->invent_number }} - {{ $lastTablet->serial_number }}
                            </a>
                            <div class="flex items-center space-x-2 text-sm">
                                <!-- Кнопка печати -->
                                <form action="/print-act/{{$employee->id}}/{{$lastTablet->id}}" method="POST">
                                    @csrf
                                    <button class="bg-blue-400 hover:bg-blue-500 text-white font-medium py-1 px-3 rounded-md shadow-sm transition-all">
                                        🖨️ Print
                                    </button>
                                </form>

                                <x-pdf-upload-form :employee="$employee" :tablet="$lastTablet"/>

                                <!-- Дополнительная форма -->
                                <x-unassign-tablet-button :employee="$employee" :tablet="$lastTablet"/>
                                <x-unassign-with-pdf-button :employee="$employee" :tablet="$lastTablet"/>

                                @if (!$lastTablet->pivot->confirmed)
                                    <form action="{{ route('confirm.tablet', [$employee->id, $lastTablet->id]) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="bg-green-400 hover:bg-green-500 text-white font-medium py-1 px-3 rounded-md shadow-sm transition-all">
                                            ✅ Confirm
                                        </button>
                                    </form>
                                @else
                                    <span class="text-green-600 font-medium">✔️ Confirmed</span>
                                @endif
                            </div>

                        </li>

                    {{-- @endforeach --}}
                </ul>
            </p>
        </div>
    @else
        <p class="text-lg text-gray-600">No tablets assigned</p>
        <form action="/assign-tablet/{{$employee->id}}" method="POST" class="mt-4">
            @csrf
            <label for="tablet" class="block text-sm font-medium text-gray-600">Assign Tablet</label>
            <select id="tablet" name="tablet_id" class="w-full p-3 border rounded-lg mt-2">
                <option value="">No Tablet</option>
                @foreach ($availableTablets as $tablet)
                    <option value="{{ $tablet->id }}">{{ $tablet->invent_number }} - {{ $tablet->serial_number }} - {{ $tablet->employees->last()->full_name ?? '' }}</option>
                @endforeach
            </select>
            <input type="date" name="unassigned_at" id="unassigned_at" value="{{ now()->format('Y-m-d') }}">
            <button type="submit" class="btn-primary mt-4 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Assign</button>
        </form>
    @endif

    <!-- Tablet Assign Form -->
    {{-- <form action="/assign-tablet/{{$employee->id}}" method="GET" class="mt-4">
        @csrf
        <label for="tablet_search" class="block text-sm font-medium text-gray-600">Search Tablet</label>
        <input type="text" id="tablet_search" name="search" class="w-full p-3 border rounded-lg mt-2" placeholder="Search by serial or invent number" value="{{ request('search') }}">

        <button type="submit" class="btn-primary mt-2 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Search</button>
    </form> --}}


</div>

<div class="bg-white shadow-md rounded-lg p-4 mt-6">
    <button onclick="toggleHistory()" class="w-full text-left font-semibold text-lg text-gray-700 border-b pb-2 mb-3 flex justify-between items-center">
        История планшетов
        <svg id="arrowIcon" class="w-5 h-5 transition-transform transform rotate-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <ul id="historyList" class="text-sm text-gray-600 space-y-2 hidden">
        @foreach($tabletHistories as $history)
            <li class="flex justify-between items-center border-b py-2">
                <div>
                    <span class="font-medium text-gray-800">
                        {{ $history->id }}
                    </span>
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
