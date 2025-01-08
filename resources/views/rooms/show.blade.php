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
                        @if($room->creator_id === auth()->id() && $room->movies->count() >= 2)
                            <div class="flex gap-2">
                                @if(!$room["elimination_started"])
                                <button onclick="toggleElimination()"
                                        id="eliminationButton"
                                        class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                    Start Elimination
                                </button>
                                @endif
                                @if($room->elimination_started)
                                    <button onclick="resetElimination()"
                                            class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 transition">
                                        Reset
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Elimination Status -->
                    <div id="eliminationStatus" class="hidden mb-4">
                        <div class="text-center p-4 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                            <h4 class="text-lg font-semibold mb-2">Elimination in Progress</h4>
                            <p id="remainingCount" class="text-gray-600 dark:text-gray-400"></p>
                        </div>
                    </div>

                    <!-- Winner Display -->
                    <div id="winnerDisplay" class="hidden mb-4">
                        <div class="text-center p-4 bg-green-100 dark:bg-green-900 rounded-lg">
                            <h4 class="text-xl font-bold mb-2">🏆 Winner! 🏆</h4>
                            <div id="winnerMovie" class="space-y-2"></div>
                        </div>
                    </div>

                    <div id="moviesList" class="divide-y dark:divide-gray-700">
                        @foreach($room->movies as $movie)
                            <div class="py-4 flex justify-between items-center movie-item transition-all duration-500"
                                 data-movie-id="{{ $movie->id }}">
                                <div class="flex-1">
                                    <h4 class="font-medium">{{ $movie->title }}</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $movie->director }} ({{ $movie->year }})
                                    </p>
                                    @if($movie->pivot->eliminated_at)
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 rounded-full mt-1">
                                            Eliminated
                                        </span>
                                    @endif
                                </div>
                                @if($movie->pivot->user_id === auth()->id() && !$room->elimination_started)
                                    <button onclick="removeMovie({{ $movie->id }})"
                                            class="text-red-600 hover:text-red-700 transition">
                                        Remove
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Movie Management -->
            @if(!$room->elimination_started)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Add Movies</h3>
                        <div class="flex gap-4">
                            <input type="text" id="movieSearch"
                                   class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                                   placeholder="Search for movies..."
                                   onkeypress="if(event.key === 'Enter') searchMovies()">
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

    <div id="chat" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Chat</h3>
            <div id="messages" class="space-y-4">
    @foreach($messages as $message)
        <!-- Afficher uniquement les messages parents (sans parentMessage) -->
        @if(!$message->parentMessage)
            <div class="flex justify-between items-start space-x-4">
                <div class="flex-1">
                    <div class="font-semibold">{{ $message->user->name }}</div> <!-- Nom de l'utilisateur -->

                    <!-- Affichage du message parent -->
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $message->message }}</p>
                    <button type="button" onclick="replyToMessage({{ $message->id }}, '{{ $message->user->name }}')" class="text-blue-500 hover:underline">Reply</button>

                    <!-- Affichage des réponses sous le message parent -->
                    @foreach($message->replies as $reply)
                        <div class="ml-4 mt-2 bg-gray-100 p-2 rounded">
                            <div class="font-semibold">{{ $reply->user->name }}</div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $reply->message }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach
</div>


            <div id="reply-to-message" class="mt-4 text-sm text-gray-500 dark:text-gray-400 hidden">
                <strong>Replying to:</strong> <span id="reply-user-name"></span>
            </div>  

            <form id="chatForm" class="mt-4" method="POST" action="{{ route('messages.store', $room->id) }}">
                @csrf
                <textarea name="message" id="message" class="w-full p-3 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" rows="4" placeholder="Type a message..." required></textarea>
                <input type="hidden" name="parent_message_id" id="parent_message_id">
                <button type="submit" class="mt-2 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">Send</button>
            </form>

        </div>
    </div>


    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
        <script>
            let eliminationInterval;
            const ELIMINATION_INTERVAL = 3000;

            async function toggleElimination() {
                const button = document.getElementById('eliminationButton');

                if (!button.disabled) {
                    try {
                        const response = await fetch(`/rooms/{{ $room->id }}/elimination/start`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        if (response.ok) {
                            button.disabled = true;
                            button.textContent = 'Elimination in Progress';
                            document.getElementById('eliminationStatus').classList.remove('hidden');
                            startEliminationProcess();
                        }
                    } catch (error) {
                        console.error('Error starting elimination:', error);
                    }
                }
            }

            async function resetElimination() {
                if (confirm('Are you sure you want to reset the elimination? This will restore all movies.')) {
                    try {
                        const response = await fetch(`/rooms/{{ $room->id }}/elimination/reset`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        if (response.ok) {
                            window.location.reload();
                        }
                    } catch (error) {
                        console.error('Error resetting elimination:', error);
                    }
                }
            }

            async function startEliminationProcess() {
                eliminationInterval = setInterval(async () => {
                    try {
                        const response = await fetch(`/rooms/{{ $room->id }}/elimination/eliminate`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        const data = await response.json();

                        if (data.status === 'eliminated') {
                            updateUI(data);
                        } else if (data.status === 'completed') {
                            clearInterval(eliminationInterval);
                            showWinner(data.winner);
                        }
                    } catch (error) {
                        console.error('Error during elimination:', error);
                    }
                }, ELIMINATION_INTERVAL);
            }

            function updateUI(data) {
                // Update remaining count
                document.getElementById('remainingCount').textContent =
                    `${data.remaining_count} movies remaining`;

                // Fade out eliminated movie
                const movieElement = document.querySelector(`[data-movie-id="${data.eliminated_movie.id}"]`);
                if (movieElement) {
                    movieElement.classList.add('opacity-25');
                    const eliminatedBadge = document.createElement('div');
                    eliminatedBadge.className = 'mt-2';
                    eliminatedBadge.innerHTML = `
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                            Eliminated
                        </span>
                    `;
                    movieElement.querySelector('.flex-1').appendChild(eliminatedBadge);

                    // Add animation
                    movieElement.classList.add('scale-95');
                    setTimeout(() => {
                        movieElement.classList.add('transform', 'rotate-3');
                    }, 300);
                }
            }

            function showWinner(winner) {
                document.getElementById('eliminationStatus').classList.add('hidden');
                const winnerDisplay = document.getElementById('winnerDisplay');
                const winnerMovie = document.getElementById('winnerMovie');

                winnerMovie.innerHTML = `
                    <div class="flex justify-center">
                        <img src="${winner.poster_url || 'https://via.placeholder.com/200x300?text=No+Poster'}"
                             alt="${winner.title}"
                             class="w-48 h-72 object-cover rounded-lg shadow-lg">
                    </div>
                    <h3 class="text-xl font-bold mt-4">${winner.title}</h3>
                    <p class="text-lg">${winner.director} (${winner.year})</p>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">${winner.plot || ''}</p>
                `;

                winnerDisplay.classList.remove('hidden');

                // Trigger confetti
                confetti({
                    particleCount: 100,
                    spread: 70,
                    origin: { y: 0.6 }
                });
            }

            // Poll for status updates to handle page refreshes
            async function pollEliminationStatus() {
                const response = await fetch(`/rooms/{{ $room->id }}/elimination/status`);
                const data = await response.json();

                if (data.elimination_in_progress) {
                    document.getElementById('eliminationButton')?.setAttribute('disabled', 'disabled');
                    document.getElementById('eliminationStatus').classList.remove('hidden');

                    if (data.remaining_movies.length === 1) {
                        showWinner(data.remaining_movies[0]);
                    } else {
                        document.getElementById('remainingCount').textContent =
                            `${data.remaining_movies.length} movies remaining`;

                        // Mark eliminated movies
                        data.eliminated_movies.forEach(movie => {
                            const movieElement = document.querySelector(`[data-movie-id="${movie.id}"]`);
                            if (movieElement) {
                                movieElement.classList.add('opacity-25', 'scale-95', 'rotate-3');
                                if (!movieElement.querySelector('.inline-flex')) {
                                    const eliminatedBadge = document.createElement('div');
                                    eliminatedBadge.className = 'mt-2';
                                    eliminatedBadge.innerHTML = `
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Eliminated
                                        </span>
                                    `;
                                    movieElement.querySelector('.flex-1').appendChild(eliminatedBadge);
                                }
                            }
                        });
                    }
                }
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
                        <img src="${movie.Poster !== 'N/A' ? movie.Poster : 'https://via.placeholder.com/96x144?text=No+Poster'}"
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

            // Start polling when page loads
            setInterval(pollEliminationStatus, 5000);
            pollEliminationStatus();

            // Add keyboard shortcut for movie search
            document.getElementById('movieSearch').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchMovies();
                }
            });
            
            function replyToMessage(messageId, userName) {
                // Mettre à jour le champ caché pour parent_message_id
                document.getElementById('parent_message_id').value = messageId;
                
                // Afficher le message de réponse avec le nom de l'utilisateur
                const replyMessageDiv = document.getElementById('reply-to-message');
                const replyUserNameSpan = document.getElementById('reply-user-name');
                replyUserNameSpan.textContent = userName;
                replyMessageDiv.classList.remove('hidden');
            }


            // Cibler le formulaire
            document.getElementById('chatForm').addEventListener('submit', function(e) {
                // Empêcher le rechargement de la page
                e.preventDefault();

                // Récupérer les données du formulaire
                var formData = new FormData(this);

                // Créer une requête AJAX
                fetch("{{ route('messages.store', $room->id) }}", {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    }
                })
                .then(response => response.json()) // Si la réponse est au format JSON
                .then(data => {
                    if (data.success) {
                        // Ajouter le message à la liste sans recharger la page
                        var messagesContainer = document.getElementById('messages');
                        var messageHTML = `
                            <div class="flex justify-between items-start space-x-4">
                                <div class="flex-1">
                                    <div class="font-semibold">${data.user_name}</div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">${data.message}</p>
                                    <button type="button" onclick="replyToMessage(${data.id}, '${data.user_name}')" class="text-blue-500 hover:underline">Reply</button>
                                </div>
                            </div>
                        `;
                        messagesContainer.innerHTML = messageHTML + messagesContainer.innerHTML; // Ajoute en haut
                    } else {
                        // Gérer l'échec du message
                        alert(data.error || 'Message could not be sent');
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("Something went wrong!");
                });
            });

            
        </script>
    @endpush
</x-app-layout>
