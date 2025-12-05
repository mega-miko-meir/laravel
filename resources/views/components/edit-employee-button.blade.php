@props(['employee'])

<div>
    <a href="/edit-employee/{{ $employee->id }}"
        class="bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-medium py-1 px-3 rounded transition">
         Edit
     </a>
</div>

