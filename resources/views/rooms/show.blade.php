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
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Movie List -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Movies in this Room</h3>
                    </div>

                    <div class="divide-y dark:divide-gray-700">
                        @foreach($room->movies->unique('imdb_id') as $movie)
                            <div class="py-3 flex justify-between items-center">
                                <div class="flex-1">
                                    <span class="font-medium">{{ $movie->title }}</span>
                                    <span class="text-gray-600 dark:text-gray-400"> - {{ $movie->director }} - {{ $movie->year }}</span>
                                </div>
                                <div class="flex gap-2">
                                    @if($movie->pivot && $movie->pivot->user_id === auth()->id())
                                        <button onclick="removeMovie({{ $movie->id }})"
                                                class="text-red-600 hover:text-red-700 transition">
                                            Remove
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Movie Management -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Add Movies</h3>
                    <div class="flex gap-4">
                        <input type="text" id="movieSearch"
                               class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                               placeholder="Search for movies...">
                        <button onclick="searchMovies()"
                                class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">
                            Search
                        </button>
                    </div>
                    <div id="searchResults" class="hidden mt-4">
                        <h4 class="font-medium mb-2">Search Results</h4>
                        <div id="movieResults" class="divide-y dark:divide-gray-700"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function searchMovies() {
            const search = document.getElementById('movieSearch').value;
            if (!search) return;

            fetch(`/rooms/{{ $room->id }}/movies/search?search=${encodeURIComponent(search)}`)
                .then(response => response.json())
                .then(data => {
                    const resultsDiv = document.getElementById('movieResults');
                    resultsDiv.innerHTML = '';

                    data.movies.forEach(movie => {
                        const movieCard = createMovieSearchCard(movie);
                        resultsDiv.appendChild(movieCard);
                    });

                    document.getElementById('searchResults').classList.remove('hidden');
                });
        }

        function createMovieSearchCard(movie) {
            const div = document.createElement('div');
            div.className = 'border dark:border-gray-700 rounded-lg p-4';
            div.innerHTML = `
                <div class="flex gap-4">
                    <img src="${movie.Poster !== 'N/A' ? movie.Poster : '/placeholder.jpg'}"
                         alt="${movie.Title}"
                         class="w-24 h-36 object-cover rounded">
                    <div class="flex-1">
                        <h4 class="font-medium">${movie.Title}</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">${movie.Year}</p>
                        <button onclick="addMovie('${movie.imdbID}')"
                                class="mt-2 bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 transition">
                            Add Movie
                        </button>
                    </div>
                </div>
            `;
            return div;
        }

        function addMovie(imdbId) {
            fetch(`/rooms/{{ $room->id }}/movies`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ imdb_id: imdbId })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        window.location.reload();
                    }
                });
        }

        function removeMovie(movieId) {
            if (!confirm('Are you sure you want to remove this movie?')) return;

            fetch(`/rooms/{{ $room->id }}/movies/${movieId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        window.location.reload();
                    }
                });
        }
    </script>
</x-app-layout>
