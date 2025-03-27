@props(['employee', 'tablet'])


<!-- Кнопка отвязки -->
<form action="{{ route('unassign-tablet', ['employee' => $employee->id, 'tablet' => $tablet->id]) }}" method="POST"
    onsubmit="return confirm('Are you sure?');">
  @csrf
  <input type="date" name="returned_at" id="returned_at" value="{{now()->format("Y-d-m")}}">
  <button type="submit" class="bg-red-400 hover:bg-red-500 text-white font-medium py-1 px-3 rounded-md shadow-sm transition-all">
      ❌ Unassign
  </button>
</form>


