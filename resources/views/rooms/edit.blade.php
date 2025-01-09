<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Update Room') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('rooms.update', $room) }}" class="max-w-md mx-auto">
                        @csrf
                        @method('PUT')

                        <!-- Room Name -->
                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Room Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" 
                                          type="text" 
                                          name="name" 
                                          value="{{ old('name', $room->name) }}" 
                                          required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Password -->
                        @if($room->password)
                            <div class="mb-4">
                                <x-input-label for="current_password" :value="__('Current Password')" />
                                <x-text-input type="password" name="current_password" class="block mt-1 w-full" id="current_password"  
                                    placeholder="Enter your current password" required />
                                <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
                            </div>
                        @endif

                        <!-- New Password -->
                        <div class="mb-4">
                            <x-input-label for="new_password" :value="__('New Password')" />
                            <x-text-input type="password" name="new_password" id="new_password" 
                                class="block mt-1 w-full" 
                                placeholder="Enter a new password (leave empty if not changing)" />
                            <x-input-error :messages="$errors->get('new_password')" class="mt-2" />
                        </div>

                        @if ($room->password)
                            <div class="mb-4">
                                <label for="remove_password" class="block text-sm font-medium text-gray-700">Remove Password</label>
                                <input type="checkbox" name="remove_password" id="remove_password" 
                                    class="w-full" 
                                    {{ old('remove_password') ? 'checked' : '' }}>
                                <span class="text-sm text-gray-500">Check this box to remove the room's password.</span>
                            </div>
                        @endif

                        <!-- Update Button -->
                        <div class="flex justify-end">
                            <x-primary-button>
                                {{ __('Update Room') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
