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
                        <div class="mb-4">
                            <x-input-label for="password" :value="__('Password (Optional)')" />
                            <x-text-input id="password" class="block mt-1 w-full" 
                                          type="password" 
                                          name="password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            <small class="text-gray-600 dark:text-gray-400">
                                {{ __('Leave blank to keep the current password.') }}
                            </small>
                        </div>

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
