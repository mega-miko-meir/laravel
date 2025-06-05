@props(['employee', 'tablet'])

<div>
    <!-- PDF -->
    @if($tablet->pdfAssignment && $tablet->pdfAssignment->pdf_path)
        <a href="{{ asset('storage/' . $tablet->pdfAssignment->pdf_path) }}" target="_blank"
        class="text-blue-600 hover:text-blue-700 underline font-medium transition-all">
            📄 PDF1
        </a>
    @else
        <form action="/upload-assign-pdf/{{ $employee->id }}/{{ $tablet->id }}" method="POST" enctype="multipart/form-data"
            class="flex items-center space-x-1 border border-gray-300 rounded-md p-1 shadow-sm">
            @csrf
            <input type="file" name="pdf_file" accept="application/pdf"
                class="text-gray-700 text-xs border-none focus:ring-0">
            <input type="date" name="assigned_at" id="assigned_at" value="{{now()->format('Y-m-d')}}">
            <button type="submit" class="bg-green-400 hover:bg-green-500 text-white font-medium py-1 px-3 rounded-md shadow-sm transition-all">
                ⬆️ Confirm
            </button>
        </form>
    @endif
</div>
