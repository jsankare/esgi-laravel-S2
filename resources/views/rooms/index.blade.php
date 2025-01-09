<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Rooms') }}
            </h2>
            <button onclick="window.location.href='{{ route('rooms.create') }}'" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">
                Create Room
            </button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($rooms->isEmpty())
                        <p class="text-center py-4">No rooms available. Create one to get started!</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($rooms as $room)
                                <div class="border dark:border-gray-700 rounded-lg p-4">
                                    <div class="flex items-start justify-between mb-4">
                                        <div>
                                            <h3 class="text-lg font-semibold">{{ $room->name }}</h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                {{ $room->users->count() }} members
                                            </p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                Created by: {{ $room->creator->name }}
                                            </p>
                                        </div>
                                        @if($room->password)
                                            <span class="text-gray-500 dark:text-gray-400">
                                                🔒
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex gap-2">
                                        @if($room->users->contains(auth()->id()))
                                            <a href="{{ route('rooms.show', $room) }}"
                                               class="flex-1 block text-center bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
                                                View Room
                                            </a>
                                            @if($room->creator_id === auth()->id())
                                                <a href="{{ route('rooms.edit', $room) }}"
                                                class="bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600 transition">
                                                    Update
                                                </a>
                                                <form action="{{ route('rooms.destroy', $room) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" onclick="return confirm('Are you sure you want to delete this room?')"
                                                            class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition">
                                                        Delete
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            <button
                                                onclick="joinRoom('{{ $room->id }}', '{{ $room->password ? 'true' : 'false' }}')"
                                                class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition">
                                                Join Room
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Password Modal -->
    <div id="passwordModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Enter Room Password</h3>
                <form id="joinForm" method="POST" action="">
                    @csrf
                    <div class="mb-4">
                        <x-text-input type="password" name="password" id="roomPassword"
                                      class="w-full" placeholder="Password" required />
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeModal()"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Join
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function joinRoom(roomId, hasPassword) {
            if (hasPassword === 'true') {
                const modal = document.getElementById('passwordModal');
                const form = document.getElementById('joinForm');
                form.action = `/rooms/${roomId}/join`;
                modal.classList.remove('hidden');
            } else {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/rooms/${roomId}/join`;
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function closeModal() {
            document.getElementById('passwordModal').classList.add('hidden');
            document.getElementById('roomPassword').value = '';
        }
    </script>
</x-app-layout>
