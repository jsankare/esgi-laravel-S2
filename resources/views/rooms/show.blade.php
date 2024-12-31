<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $room->name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('rooms.index') }}"
                   class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition">
                    Back to Rooms
                </a>
                @if($room->creator_id === auth()->id())
                    <form action="{{ route('rooms.destroy', $room) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Are you sure you want to delete this room?')"
                                class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition">
                            Delete Room
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Room Details</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Created by: {{ $room->creator->name }}</p>
                    </div>

                    <h3 class="text-lg font-semibold mb-4">Members ({{ $room->users->count() }})</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($room->users as $user)
                            <div class="flex items-center space-x-3 p-3 border dark:border-gray-700 rounded-lg">
                                <div class="flex-1">
                                    <p class="font-medium">{{ $user->name }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $user->email }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
