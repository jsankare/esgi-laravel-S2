<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $actor['name'] }}
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
                        <!-- Actor Profile Picture -->
                        <div class="w-full md:w-1/3">
                            @if($actor['profile_path'] && $actor['profile_path'] !== 'N/A')
                                <img src="{{ $actor['profile_path'] }}" alt="{{ $actor['name'] }}" class="w-full rounded-lg shadow-lg">
                            @else
                                <div class="w-full h-[600px] bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                    <span class="text-gray-400 dark:text-gray-500">No Image Available</span>
                                </div>
                            @endif
                        </div>

                        <!-- Actor Details -->
                        <div class="w-full md:w-2/3">
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">{{ $actor['name'] }}</h1>

                            <div class="space-y-4">
                                <p class="text-gray-700 dark:text-gray-300">{{ $actor['biography'] }}</p>

                                <div class="flex gap-4 text-sm text-gray-600 dark:text-gray-400">
                                    <span><strong>Birthday:</strong> {{ $actor['birthday'] }}</span>
                                    <span><strong>Place of Birth:</strong> {{ $actor['place_of_birth'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Movies Carousel Section -->
                <div class="p-6">
                    <h2 class="text-xl font-semibold mt-6">Movies</h2>
                    <!-- Carousel -->
                    <div class="relative">
                        <div class="carousel-container flex space-x-4 pb-4 w-full overflow-hidden scrollbar-none">
                            @foreach ($actor['movies'] as $movie)
                                <div class="carousel-item flex-none w-48 h-80 bg-gray-200 dark:bg-gray-700 rounded-lg shadow-lg">
                                    <a href="{{ route('movies.show', $movie['id']) }}" class="block h-full">
                                        @if(isset($movie['poster_path']) && $movie['poster_path'])
                                            <img src="https://image.tmdb.org/t/p/original/{{ $movie['poster_path'] }}" alt="{{ $movie['title'] }}" class="w-full h-3/4 object-cover rounded-t-lg">
                                        @else
                                            <div class="w-full h-3/4 bg-gray-200 dark:bg-gray-700 flex items-center justify-center rounded-t-lg">
                                                <span class="text-gray-400 dark:text-gray-500">No Poster Available</span>
                                            </div>
                                        @endif
                                        <div class="p-4">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $movie['title'] }}</h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ substr($movie['release_date'], 0, 4) }}</p>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>

                        <!-- Carousel Navigation Buttons -->
                        <button class="absolute top-1/2 left-0 transform -translate-y-1/2 bg-gray-800 text-white px-3 py-2 rounded-full focus:outline-none hover:bg-gray-900 transition" id="prevBtn">
                            &#8592;
                        </button>
                        <button class="absolute top-1/2 right-0 transform -translate-y-1/2 bg-gray-800 text-white px-3 py-2 rounded-full focus:outline-none hover:bg-gray-900 transition" id="nextBtn">
                            &#8594;
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Carousel functionality
        document.addEventListener('DOMContentLoaded', function () {
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const carouselContainer = document.querySelector('.carousel-container');
            const items = document.querySelectorAll('.carousel-item');
            let scrollAmount = 0;
            const itemWidth = items[0].offsetWidth + 16; // width of one item + space between items

            prevBtn.addEventListener('click', function () {
                if (scrollAmount > 0) {
                    scrollAmount -= itemWidth;
                    carouselContainer.scrollTo({
                        left: scrollAmount,
                        behavior: 'smooth'
                    });
                }
            });

            nextBtn.addEventListener('click', function () {
                if (scrollAmount < carouselContainer.scrollWidth - carouselContainer.offsetWidth) {
                    scrollAmount += itemWidth;
                    carouselContainer.scrollTo({
                        left: scrollAmount,
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</x-app-layout>
