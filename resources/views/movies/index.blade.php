<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Movies') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Search and Filter Form -->
                    <form method="GET" action="{{ route('movies.index') }}" class="mb-6">
                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="flex-1">
                                <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                                <input type="text" name="search" id="search" value="{{ $search }}"
                                       class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm"
                                       placeholder="Search movies...">
                            </div>
                            <div class="w-full md:w-48">
                                <label for="genre" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Genre</label>
                                <select name="genre" id="genre"
                                        class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm">
                                    <option value="">All Genres</option>
                                    @foreach($genres as $genre)
                                        <option value="{{ $genre }}" {{ $selectedGenre == $genre ? 'selected' : '' }}>
                                            {{ $genre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit"
                                        class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">
                                    Filter
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Movies Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse($movies as $movie)
                            <a href="{{ route('movies.show', $movie['imdbID']) }}" class="block">
                                <div class="border dark:border-gray-700 rounded-lg overflow-hidden hover:shadow-lg transition">
                                    @if($movie['Poster'] && $movie['Poster'] !== 'N/A')
                                        <img src="{{ $movie['Poster'] }}" alt="{{ $movie['Title'] }}" class="w-full h-96 object-cover">
                                    @else
                                        <div class="w-full h-96 bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                            <span class="text-gray-400 dark:text-gray-500">No Poster Available</span>
                                        </div>
                                    @endif
                                    <div class="p-4">
                                        <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-white">{{ $movie['Title'] }}</h3>
                                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                            <p>Year: {{ $movie['Year'] }}</p>
                                            <p>Genre: {{ $movie['Genre'] }}</p>
                                            <p>Director: {{ $movie['Director'] }}</p>
                                        </div>
                                        <p class="text-sm line-clamp-3 text-gray-700 dark:text-gray-300">{{ $movie['Plot'] }}</p>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="col-span-3 text-center py-10">
                                <p class="text-gray-500 dark:text-gray-400">No movies found.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    @if($total > 10)
                        <div class="mt-6 flex justify-center">
                            <div class="flex gap-2">
                                @for($i = 1; $i <= min(ceil($total / 10), 10); $i++)
                                    <a href="{{ route('movies.index', ['page' => $i, 'search' => $search, 'genre' => $selectedGenre]) }}"
                                       class="px-4 py-2 border dark:border-gray-700 rounded-md {{ $currentPage == $i ? 'bg-indigo-600 text-white' : 'hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                                        {{ $i }}
                                    </a>
                                @endfor
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
