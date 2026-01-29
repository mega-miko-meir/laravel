@props(['employee', 'tablet'])

<div class="flex flex-col gap-1 text-sm">

    <!-- Если PDF уже загружен -->
    @if($tablet->pdfAssignment && $tablet->pdfAssignment->pdf_path)
        <a href="{{ asset('storage/' . $tablet->pdfAssignment->pdf_path) }}" target="_blank"
           class="text-blue-600 hover:text-blue-700 underline font-medium text-xs transition-all">
            📄 View PDF
        </a>
    @else
        <form action="/upload-assign-pdf/{{ $employee->id }}/{{ $tablet->id }}" method="POST" enctype="multipart/form-data"
              class="flex items-center gap-1 border border-gray-300 rounded-md p-1 shadow-sm bg-gray-50">
            @csrf

            <input type="file" name="pdf_file" accept="application/pdf"
                   class="text-gray-700 text-xs border-none focus:ring-0">

            <input type="date" name="assigned_at" value="{{ now()->format('Y-m-d') }}"
                   class="text-xs border border-gray-300 rounded px-1 py-0.5">

            <button type="submit"
                    class="bg-green-400 hover:bg-green-500 text-white text-xs font-semibold py-1 px-2 rounded shadow-sm transition-all">
                ⬆️ Upload
            </button>
        </form>
    @endif
</div>
