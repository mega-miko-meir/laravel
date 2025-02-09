@props(['employee', 'availableTablets', 'tabletHistories'])

<div class="mt-8 bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-xl font-semibold text-gray-800">Tablet Assignment</h2>

    @if($employee->tablets->isNotEmpty())
        <div class="mt-4">
            <p class="text-lg text-gray-600">
                <span class="font-medium text-gray-800">Tablets:</span>
                <ul class="space-y-4">
                    @foreach($employee->tablets as $tablet)
                        <li class="flex items-center justify-between text-sm text-gray-600 py-2 border-b border-gray-200">
                            <a href="{{route('tablets.show', $tablet->id)}}" class="text-blue-500 hover:underline">
                                {{ $tablet->invent_number }} - {{ $tablet->serial_number }}
                            </a>
                            <div class="flex items-center space-x-2 text-sm">
                                <!-- Кнопка печати -->
                                <form action="/print-act/{{$employee->id}}/{{$tablet->id}}" method="POST">
                                    @csrf
                                    <button class="bg-blue-400 hover:bg-blue-500 text-white font-medium py-1 px-3 rounded-md shadow-sm transition-all">
                                        🖨️ Print
                                    </button>
                                </form>

                                <!-- PDF -->
                                @if($tablet->pdfAssignment && $tablet->pdfAssignment->pdf_path)
                                    <a href="{{ asset('storage/' . $tablet->pdfAssignment->pdf_path) }}" target="_blank"
                                       class="text-blue-600 hover:text-blue-700 underline font-medium transition-all">
                                        📄 Открыть PDF
                                    </a>
                                @else
                                    <form action="/upload-assign-pdf/{{ $employee->id }}/{{ $tablet->id }}" method="POST" enctype="multipart/form-data"
                                          class="flex items-center space-x-1 border border-gray-300 rounded-md p-1 shadow-sm">
                                        @csrf
                                        <input type="file" name="pdf_file" accept="application/pdf" required
                                               class="text-gray-700 text-xs border-none focus:ring-0">
                                        <button type="submit" class="bg-green-400 hover:bg-green-500 text-white font-medium py-1 px-3 rounded-md shadow-sm transition-all">
                                            ⬆️ Загрузить
                                        </button>
                                    </form>
                                @endif

                                <!-- Кнопка отвязки -->
                                <form action="{{ route('unassign-tablet', ['employee' => $employee->id, 'tablet' => $tablet->id]) }}" method="POST"
                                      onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    <button type="submit" class="bg-red-400 hover:bg-red-500 text-white font-medium py-1 px-3 rounded-md shadow-sm transition-all">
                                        ❌ Unassign
                                    </button>
                                </form>

                                <!-- Дополнительная форма -->
                                <x-dark-form :employee="$employee" :tablet="$tablet"/>
                            </div>

                        </li>

                    @endforeach
                </ul>
            </p>
        </div>
    @else
        <p class="text-lg text-gray-600">No tablets assigned</p>
    @endif

    <!-- Tablet Assign Form -->
    <form action="/assign-tablet/{{$employee->id}}" method="POST" class="mt-4">
        @csrf
        <label for="tablet" class="block text-sm font-medium text-gray-600">Assign Tablet</label>
        <select id="tablet" name="tablet_id" class="w-full p-3 border rounded-lg mt-2">
            <option value="">No Tablet</option>
            @foreach ($availableTablets as $tablet)
                <option value="{{ $tablet->id }}">{{ $tablet->invent_number }} - {{ $tablet->serial_number }} - {{ $tablet->old_employee_id ?? ''}}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-primary mt-4 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Assign</button>
    </form>
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
                        {{ $history->tablet ? $history->tablet->serial_number : 'Неизвестный планшет' }}
                    </span>
                    <span class="text-sm text-gray-500 ml-2">
                        {{ \Carbon\Carbon::parse($history->assigned_at)->format('d.m.Y') }} -
                        {{ $history->returned_at ? \Carbon\Carbon::parse($history->returned_at)->format('d.m.Y') : 'Текущий пользователь' }}
                    </span>
                </div>
                <div class="space-x-3">
                    @if($history->pdf_path)
                        <a href="{{ asset('storage/' . $history->pdf_path) }}" target="_blank" class="text-blue-500 hover:underline">PDF1</a>
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
