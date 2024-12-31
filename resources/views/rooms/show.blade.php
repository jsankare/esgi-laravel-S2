<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $room->name }}
            </h2>
            <div class="flex gap-2">
                @if(!$room->game_started && $room->creator_id === auth()->id())
                    <button onclick="startGame()"
                            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition">
                        Start Elimination Game
                    </button>
                @endif
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
                        @if($room->game_started)
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Current turn: {{ $currentPlayer->name }}
                            </p>
                        @endif
                    </div>

                    <div class="divide-y dark:divide-gray-700">
                        @foreach($room->movies->unique('imdb_id') as $movie)
                            <div class="py-3 flex justify-between items-center">
                                <div class="flex-1">
                                    <span class="font-medium">{{ $movie->title }}</span>
                                    <span class="text-gray-600 dark:text-gray-400"> - {{ $movie->director }} - {{ $movie->year }}</span>
                                </div>
                                <div class="flex gap-2">
                                    @if(!$room->game_started && $movie->pivot && $movie->pivot->user_id === auth()->id())
                                        <button onclick="removeMovie({{ $movie->id }})"
                                                class="text-red-600 hover:text-red-700 transition">
                                            Remove
                                        </button>
                                    @endif
                                    @if($room->game_started && $currentPlayer->id === auth()->id() && !$movie->eliminated_at)
                                        <button onclick="eliminateMovie({{ $movie->id }})"
                                                class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700 transition">
                                            Eliminate
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($room->game_started && $room->movies->where('eliminated_at', null)->count() === 1)
                        <div class="mt-4 p-4 bg-green-100 dark:bg-green-900 rounded-lg">
                            <h4 class="font-semibold text-green-800 dark:text-green-200">Winner!</h4>
                            <p class="text-green-700 dark:text-green-300">
                                {{ $room->movies->where('eliminated_at', null)->first()->title }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            @if(!$room->game_started)
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
            @endif
        </div>
    </div>

    <script>
        function startGame() {
            if (!confirm('Are you sure you want to start the elimination game? This will lock the movie list.')) return;

            fetch(`/rooms/{{ $room->id }}/start-game`, {
                method: 'POST',
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

        function eliminateMovie(movieId) {
            if (!confirm('Are you sure you want to eliminate this movie?')) return;

            fetch(`/rooms/{{ $room->id }}/movies/${movieId}/eliminate`, {
                method: 'POST',
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
