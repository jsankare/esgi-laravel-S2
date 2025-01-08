<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $movie['Title'] }}
            </h2>
            <a href="{{ route('movies.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition">
                Back to Movies
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row gap-8">
                        <!-- Movie Poster -->
                        <div class="w-full md:w-1/3">
                            @if($movie['Poster'] && $movie['Poster'] !== 'N/A')
                                <img src="{{ $movie['Poster'] }}" alt="{{ $movie['Title'] }}" class="w-full rounded-lg shadow-lg">
                            @else
                                <div class="w-full h-[600px] bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                    <span class="text-gray-400 dark:text-gray-500">No Poster Available</span>
                                </div>
                            @endif
                        </div>

                        <!-- Movie Details -->
                        <div class="w-full md:w-2/3">
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">{{ $movie['Title'] }}</h1>

                            <div class="space-y-4">
                                <div class="flex gap-4 text-sm text-gray-600 dark:text-gray-400">
                                    <span>{{ $movie['Year'] }}</span>
                                    <span>{{ $movie['Rated'] }}</span>
                                    <span>{{ $movie['Runtime'] }}</span>
                                </div>

                                <div class="flex gap-2 flex-wrap">
                                    @foreach(explode(', ', $movie['Genre']) as $genre)
                                        <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-sm">
                                            {{ $genre }}
                                        </span>
                                    @endforeach
                                </div>

                                <p class="text-gray-700 dark:text-gray-300">{{ $movie['Plot'] }}</p>

                                <div class="space-y-2">
                                    <p><span class="font-semibold">Director:</span> {{ $movie['Director'] }}</p>
                                    <p><span class="font-semibold">Writers:</span> {{ $movie['Writer'] }}</p>
                                    <p><span class="font-semibold">Actors:</span> {{ $movie['Actors'] }}</p>
                                </div>

                                @if($movie['Ratings'])
                                    <div class="mt-6">
                                        <h2 class="text-xl font-semibold mb-3">Ratings</h2>
                                        <div class="space-y-2">
                                            @foreach($movie['Ratings'] as $rating)
                                                <div>
                                                    <span class="font-medium">{{ $rating['Source'] }}:</span>
                                                    <span>{{ $rating['Value'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
