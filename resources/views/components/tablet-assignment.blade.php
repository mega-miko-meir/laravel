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
                            <span>{{ $tablet->invent_number }} - {{ $tablet->serial_number }}</span>
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

<div>
    <h3 class="font-semibold text-sm text-gray-700">History:</h3>
    <ul class="text-sm text-gray-500">
        @foreach($tabletHistories as $history)
            <li>
                <span >{{ $history->tablet ? $history->tablet->serial_number : '' }} ----- </span>
                <span class="text-sm">{{ \Carbon\Carbon::parse($history->assigned_at)->format('d.m.Y') }} - {{ $history->returned_at ? \Carbon\Carbon::parse($history->returned_at)->format('d.m.Y') : ''}}</span>
                @if($history->pdf_path)
                    - <a href="{{ asset('storage/' . $history->pdf_path) }}" target="_blank">PDF1</a>
                @endif
                @if($history->unassign_pdf)
                    - <a href="{{ asset('storage/' . $history->unassign_pdf) }}" target="_blank">PDF2</a>
                @endif

            </li>
        @endforeach
    </ul>
</div>



<script>
    document.getElementById('toggle-bricks-btn').addEventListener('click', function () {
        const bricksList = document.getElementById('bricks-list');
        bricksList.classList.toggle('hidden'); // Показываем или скрываем список
    });
</script>
