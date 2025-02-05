
@props(['employee'])

<div class="space-y-0 border-2 border-gray-300 hover:shadow-lg">
    <div class="p-4 flex items-center justify-between border-b">
        <a href="/employee/{{$employee->id}}" class="text-blue-600 hover:underline">
            <h3 class="text-lg font-bold">{{$employee->last_name}} {{$employee->first_name}}</h3>
            <p class="text-gray-700">
                @if($employee->territories->isNotEmpty())
                    Team: {{$employee->territories->first()->team}}
                    City: {{$employee->territories->first()->city}}
                @endif
            </p>
        </a>

        <div class="flex space-x-2">
            <a href="/edit-employee/{{$employee->id}}" class="btn-secondary bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded">
                Edit
            </a>
            <form action="/delete-employee/{{$employee->id}}" method="POST" onsubmit="return confirm('Are you sure you want to delete this employee?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-secondary bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>
