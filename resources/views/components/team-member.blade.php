{{-- resources/views/components/team-member.blade.php --}}
@props(['member'])

<div x-data="{ open: false }" class="bg-white p-4 rounded-xl shadow cursor-pointer w-56">
    <div @click="open = !open" class="flex items-center justify-between">
        <span class="font-bold text-gray-700 truncate">{{ $member->full_name }}</span>
        <svg :class="{'rotate-180': open}" class="w-4 h-4 text-gray-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>

    <div x-show="open" x-cloak class="mt-2 space-y-2">
        {{-- Территории сотрудника --}}
        @if($member->territories->isNotEmpty())
            <div class="text-sm text-gray-600">
                <span class="font-medium">Территории:</span>
                <ul class="ml-2 list-disc">
                    @foreach($member->territories as $territory)
                        <li>
                            <a href="{{ route('territories.show', $territory->id) }}" class="text-blue-600 hover:underline">
                                {{ $territory->territory_name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Дочерние сотрудники --}}
        @if($member->team->isNotEmpty())
            <div class="mt-2">
                <span class="font-medium text-gray-700">Команда:</span>
                <div class="flex flex-wrap gap-2 mt-1">
                    @foreach($member->team as $subMember)
                        <x-team-member :member="$subMember" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
