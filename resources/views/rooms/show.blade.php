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
            <!-- Room Users Info -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                        <span class="font-semibold">{{ $room->users->count() }} Members:</span>
                        <span>{{ $room->users->pluck('name')->join(', ') }}</span>
                    </div>
                </div>
            </div>
            <!-- Notification Container -->
            <div id="notification" class="hidden fixed top-4 right-4 z-50 max-w-sm">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-4 border-l-4 flex items-center">
                    <div id="notification-icon" class="mr-3"></div>
                    <div>
                        <p id="notification-message" class="text-sm text-white font-medium"></p>
                    </div>
                    <button onclick="hideNotification()" class="ml-4 text-gray-400 hover:text-gray-500">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Movie List -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Movies</h3>
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
                                            id="resetButton"
                                            class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 transition">
                                        Reset
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Elimination Status -->
                    <div id="eliminationStatus" class="hidden mb-6">
                        <div class="text-center p-4 bg-gradient-to-r from-red-500/10 to-orange-500/10 dark:from-red-900/30 dark:to-orange-900/30 rounded-lg border border-red-200 dark:border-red-800">
                            <p class="text-lg font-medium text-gray-800 dark:text-gray-200">Elimination in Progress</p>
                            <p id="remainingCount" class="mt-1 text-sm text-gray-600 dark:text-gray-400"></p>
                        </div>
                    </div>

                    <!-- Winner Display -->
                    <div id="winnerDisplay" class="hidden mb-6">
                        <div class="text-center p-6 bg-gradient-to-r from-green-500/10 to-emerald-500/10 dark:from-green-900/30 dark:to-emerald-900/30 rounded-lg border border-green-200 dark:border-green-800">
                            <div class="inline-block mb-4">
                                <svg class="w-12 h-12 text-green-500 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-2">Winner</h4>
                            <div id="winnerMovie" class="space-y-2"></div>
                        </div>
                    </div>

                    <!-- Movies Grid -->
                    <div id="moviesList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($room->movies as $movie)
                            <div class="relative p-4 rounded-lg border dark:border-gray-700 transition-all duration-500 movie-item
     {{ $movie->pivot->eliminated_at ? 'opacity-50 bg-gray-100 dark:bg-gray-800/50' : 'bg-white dark:bg-gray-800' }}"
                                 data-movie-id="{{ $movie->id }}">
                                <div class="flex gap-4">
                                    <img src="{{ $movie->poster_url ?: 'https://via.placeholder.com/150x225?text=No+Poster' }}"
                                         alt="{{ $movie->title }}"
                                         class="w-24 h-36 object-cover rounded-md">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $movie->title }}</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $movie->director }} ({{ $movie->year }})
                                        </p>

                                        <!-- Reactions Section -->
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            @foreach($movie->reactions->where('room_id', $room->id)->groupBy('emoji') as $emoji => $reactions)
                                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-sm">
                        {{ $emoji }} {{ $reactions->count() }}
                    </span>
                                            @endforeach
                                        </div>

                                        <!-- Reaction Controls -->
                                        <div class="mt-2 relative reaction-container">
                                            @if(!$room->elimination_started)
                                                @php
                                                    $userReaction = $movie->reactions
                                                        ->where('room_id', $room->id)
                                                        ->where('user_id', auth()->id())
                                                        ->first();
                                                @endphp

                                                @if($userReaction)
                                                    <button
                                                        onclick="removeReaction({{ $movie->id }})"
                                                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200"
                                                    >
                                                        {{ $userReaction->emoji }} Remove
                                                    </button>
                                                @else
                                                    <button
                                                        id="reaction-btn-{{ $movie->id }}"
                                                        onclick="showReactionPicker({{ $movie->id }})"
                                                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200"
                                                    >
                                                        Add Reaction
                                                    </button>
                                                @endif
                                            @endif
                                        </div>

                                        <!-- Existing movie controls -->
                                        @if($movie->pivot->eliminated_at)
                                            <span class="inline-flex items-center px-2 py-1 mt-2 text-xs font-medium rounded bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200">
                    Eliminated
                </span>
                                        @endif
                                        @if($movie->pivot->user_id === auth()->id() && !$room->elimination_started && !$room->elimination_in_progress)
                                            <button onclick="removeMovie({{ $movie->id }})"
                                                    class="mt-2 text-sm text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition">
                                                Remove
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Movie Search -->
            @if(!$room->elimination_started)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Add Movies</h3>
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
                        <!-- Loading Indicator -->
                        <div id="searchLoader" class="hidden mt-4">
                            <div class="flex items-center justify-center">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                                <span class="ml-2 text-gray-600 dark:text-gray-400">Searching...</span>
                            </div>
                        </div>
                        <div id="searchResults" class="hidden mt-4">
                            <h4 class="font-medium mb-2 text-gray-800 dark:text-gray-200">Search Results</h4>
                            <div id="movieResults" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div id="chat" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mt-6">
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Chat</h3>

            <!-- Messages Container -->
            <div id="messages" class="space-y-4 max-h-[500px] overflow-y-auto mb-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                @foreach($messages as $message)
                    @if(!$message->parentMessage)
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $message->user->name }}</span>
                                        <span class="text-xs text-gray-500">{{ $message->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-gray-700 dark:text-gray-300">{{ $message->message }}</p>
                                    <button type="button"
                                            onclick="replyToMessage({{ $message->id }}, '{{ $message->user->name }}')"
                                            class="text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 mt-2">
                                        Reply
                                    </button>

                                    <!-- Replies -->
                                    @foreach($message->replies as $reply)
                                        <div class="ml-8 mt-2 bg-gray-50 dark:bg-gray-900 p-3 rounded-lg">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $reply->user->name }}</span>
                                                <span class="text-xs text-gray-500">{{ $reply->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-gray-700 dark:text-gray-300">{{ $reply->message }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Reply Indicator -->
            <div id="reply-to-message" class="hidden mb-2 p-2 bg-indigo-50 dark:bg-indigo-900/50 rounded-lg">
                <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    <strong>Replying to:</strong> <span id="reply-user-name"></span>
                </span>
                    <button onclick="cancelReply()" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Message Form -->
            <form id="chatForm" class="mt-4" method="POST" action="{{ route('messages.store', $room->id) }}">
                @csrf
                <textarea
                    name="message"
                    id="message"
                    class="w-full p-3 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    rows="3"
                    placeholder="Type your message..."
                    required
                ></textarea>
                <input type="hidden" name="parent_message_id" id="parent_message_id">
                <button type="submit" class="mt-2 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                    Send Message
                </button>
            </form>
        </div>
    </div>


    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
        <script>
            let eliminationInterval;
            const ELIMINATION_INTERVAL = 1500;

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

                // Update eliminated movie
                const movieElement = document.querySelector(`[data-movie-id="${data.eliminated_movie.id}"]`);
                if (movieElement) {
                    movieElement.classList.add('opacity-50', 'bg-gray-100', 'dark:bg-gray-800/50');

                    // Add eliminated badge if not exists
                    if (!movieElement.querySelector('.inline-flex')) {
                        const badge = document.createElement('span');
                        badge.className = 'inline-flex items-center px-2 py-1 mt-2 text-xs font-medium rounded bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200';
                        badge.textContent = 'Eliminated';
                        movieElement.querySelector('.flex-1').appendChild(badge);
                    }
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
                    <h3 class="text-xl font-bold mt-4 text-gray-900 dark:text-gray-100">${winner.title}</h3>
                    <p class="text-gray-600 dark:text-gray-400">${winner.director} (${winner.year})</p>
                `;

                winnerDisplay.classList.remove('hidden');

                const resetButton = document.createElement('button');
                resetButton.id = 'resetButton';
                resetButton.onclick = resetElimination;
                resetButton.className = 'bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 transition mt-4';
                resetButton.textContent = 'Reset';
                if (!document.getElementById('resetButton')) {
                    winnerDisplay.appendChild(resetButton);
                }

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
                                movieElement.classList.add('opacity-25');
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

                // Show loader
                document.getElementById('searchLoader').classList.remove('hidden');
                // Hide previous results while searching
                document.getElementById('searchResults').classList.add('hidden');

                fetch(`/rooms/{{ $room->id }}/movies/search?search=${encodeURIComponent(search)}`)
                    .then(response => response.json())
                    .then(data => {
                        const resultsDiv = document.getElementById('movieResults');
                        resultsDiv.innerHTML = '';

                        data.movies.forEach(movie => {
                            const movieCard = createMovieSearchCard(movie);
                            resultsDiv.appendChild(movieCard);
                        });
                        // Hide loader
                        document.getElementById('searchLoader').classList.add('hidden');
                        // Show results
                        document.getElementById('searchResults').classList.remove('hidden');
                    })
                    .catch(error => {
                        console.error('Error searching movies:', error);
                        // Hide loader on error
                        document.getElementById('searchLoader').classList.add('hidden');
                        showNotification('Error searching movies. Please try again.', 'error');
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

            function showNotification(message, type = 'error') {
                const notification = document.getElementById('notification');
                const notificationMessage = document.getElementById('notification-message');
                const notificationIcon = document.getElementById('notification-icon');

                // Set message
                notificationMessage.textContent = message;

                // Set icon and colors based on type
                if (type === 'error') {
                    notification.querySelector('div').classList.remove('border-green-500');
                    notification.querySelector('div').classList.add('border-red-500');
                    notificationIcon.innerHTML = `
                                <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            `;
                } else {
                    notification.querySelector('div').classList.remove('border-red-500');
                    notification.querySelector('div').classList.add('border-green-500');
                    notificationIcon.innerHTML = `
                                <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"/>
                                </svg>
                            `;
                }

                // Show notification
                notification.classList.remove('hidden');

                // Hide after 3 seconds
                setTimeout(hideNotification, 3000);
            }

            function hideNotification() {
                document.getElementById('notification').classList.add('hidden');
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
                            showNotification(data.error, 'error');
                        } else {
                            showNotification('Movie added successfully!', 'success');
                            setTimeout(() => window.location.reload(), 1000);
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
                            showNotification(data.error, 'error');
                        } else {
                            showNotification('Movie removed successfully!', 'success');
                            setTimeout(() => window.location.reload(), 1000);
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

            function cancelReply() {
                document.getElementById('reply-to-message').classList.add('hidden');
                document.getElementById('parent_message_id').value = '';
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
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Ajouter le message à la liste sans recharger la page
                            var messageHTML = `
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="font-semibold text-gray-800 dark:text-gray-200">${data.user_name}</span>
                                            <span class="text-xs text-gray-500">Just now</span>
                                        </div>
                                        <p class="text-gray-700 dark:text-gray-300">${data.message}</p>
                                        <button type="button"
                                            onclick="replyToMessage(${data.id}, '${data.user_name}')"
                                            class="text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 mt-2">
                                            Reply
                                        </button>
                                    </div>
                                </div>
                            </div>
            `;

                            // Selection du dernier message
                            var messagesContainer = document.getElementById('messages');
                            var lastMessage = messagesContainer.lastChild;

                            // Creation du wrapper
                            var newMessageDiv = document.createElement('div');
                            newMessageDiv.className = 'space-y-4';
                            newMessageDiv.innerHTML = messageHTML;

                            // Insertion du message tout en bas
                            messagesContainer.insertBefore(newMessageDiv, lastMessage);

                            // Clear le field input
                            document.getElementById('message').value = '';

                            // Clear les effets de reply
                            cancelReply();

                            // Scroll
                            newMessageDiv.scrollIntoView({ behavior: 'smooth' });
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

            //Reactions
            const EMOJI_LIST = ['👍', '👎', '❤️', '🤞', '🤟', '🥲', '🥳',];

            function showReactionPicker(movieId) {
                const picker = document.createElement('div');
                picker.className = 'absolute bottom-full left-0 bg-white dark:bg-gray-800 p-2 rounded-lg shadow-lg flex gap-2 mb-2';
                picker.id = `reaction-picker-${movieId}`;

                EMOJI_LIST.forEach(emoji => {
                    const button = document.createElement('button');
                    button.className = 'hover:scale-125 transition-transform';
                    button.textContent = emoji;
                    button.onclick = () => addReaction(movieId, emoji);
                    picker.appendChild(button);
                });

                // Remove existing picker if any
                const existingPicker = document.getElementById(`reaction-picker-${movieId}`);
                if (existingPicker) {
                    existingPicker.remove();
                    return;
                }

                const container = document.querySelector(`[data-movie-id="${movieId}"] .reaction-container`);
                container.appendChild(picker);

                // Close picker when clicking outside
                document.addEventListener('click', function closePicker(e) {
                    if (!picker.contains(e.target) && e.target.id !== `reaction-btn-${movieId}`) {
                        picker.remove();
                        document.removeEventListener('click', closePicker);
                    }
                });
            }

            function addReaction(movieId, emoji) {
                fetch(`/rooms/{{ $room->id }}/movies/${movieId}/reactions`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ emoji })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove the picker
                            document.getElementById(`reaction-picker-${movieId}`).remove();
                            // Refresh the movie list or update the reactions display
                            location.reload();
                        }
                    });
            }

            function removeReaction(movieId) {
                fetch(`/rooms/{{ $room->id }}/movies/${movieId}/reactions`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        }
                    });
            }


        </script>
    @endpush
</x-app-layout>
