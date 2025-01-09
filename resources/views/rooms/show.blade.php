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

                                        <!-- Active Reactions Display -->
                                        <div class="mt-2 flex flex-wrap gap-1" id="active-reactions-{{ $movie->id }}"></div>

                                        <!-- React Button -->
                                        <button
                                            onclick="openReactionModal('{{ $movie->id }}')"
                                            class="mt-2 text-sm bg-indigo-600 text-white px-3 py-1 rounded-md hover:bg-indigo-700 transition-colors">
                                            React
                                        </button>

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

                    <!-- Reaction Modal -->
                    <div id="reactionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-sm w-full mx-4">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Add Reaction</h3>
                                <button onclick="closeReactionModal()" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="grid grid-cols-4 gap-4" id="reactionButtons"></div>
                        </div>
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

                // Keep reset button visible
                const resetButton = document.createElement('button');
                resetButton.id = 'resetButton';
                resetButton.onclick = resetElimination;
                resetButton.className = 'bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 transition mt-4';
                resetButton.textContent = 'Reset';
                // Only add the reset button if it doesn't already exist
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

            const SUPPORTED_EMOJIS = ['👍', '👎', '❤️', '😂', '😮', '😢', '🤔', '🎬'];
            let currentMovieId = null;
            const movieReactions = new Map(); // Store reactions for each movie

            function openReactionModal(movieId) {
                currentMovieId = movieId;
                const modal = document.getElementById('reactionModal');
                const buttonsContainer = document.getElementById('reactionButtons');

                // Clear existing buttons
                buttonsContainer.innerHTML = '';

                // Create reaction buttons
                SUPPORTED_EMOJIS.forEach(emoji => {
                    const button = document.createElement('button');
                    button.className = `reaction-btn p-4 text-2xl rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors
            ${isReactionActive(movieId, emoji) ? 'bg-gray-100 dark:bg-gray-700' : ''}`;
                    button.onclick = () => toggleReaction(movieId, emoji);

                    const count = getReactionCount(movieId, emoji);
                    button.innerHTML = `
            ${emoji}
            <span class="block text-xs mt-1 text-gray-600 dark:text-gray-400">${count}</span>
        `;

                    buttonsContainer.appendChild(button);
                });

                modal.classList.remove('hidden');
            }

            function closeReactionModal() {
                document.getElementById('reactionModal').classList.add('hidden');
                currentMovieId = null;
            }

            function isReactionActive(movieId, emoji) {
                const reactions = movieReactions.get(movieId) || {};
                return reactions[emoji]?.userReacted || false;
            }

            function getReactionCount(movieId, emoji) {
                const reactions = movieReactions.get(movieId) || {};
                return reactions[emoji]?.count || 0;
            }

            async function toggleReaction(movieId, emoji) {
                try {
                    const isReacted = isReactionActive(movieId, emoji);
                    const method = isReacted ? 'DELETE' : 'POST';
                    const url = `/rooms/{{ $room->id }}/movies/${movieId}/react`;

                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ emoji })
                    });

                    if (response.ok) {
                        const data = await response.json();
                        if (data.success) {
                            updateReactionState(movieId, emoji, data.reaction);
                            updateReactionUI(movieId);
                            closeReactionModal();
                            showNotification(
                                isReacted ? 'Reaction removed!' : 'Reaction added!',
                                'success'
                            );
                        }
                    } else {
                        showNotification('Failed to update reaction', 'error');
                    }
                } catch (error) {
                    console.error('Error toggling reaction:', error);
                    showNotification('Error updating reaction', 'error');
                }
            }

            function updateReactionState(movieId, emoji, reactionData) {
                let movieReactionState = movieReactions.get(movieId) || {};

                if (reactionData.user_reacted) {
                    // Remove only the user's previous reaction for this movie
                    Object.keys(movieReactionState).forEach(existingEmoji => {
                        if (movieReactionState[existingEmoji]?.userReacted) {
                            const currentCount = movieReactionState[existingEmoji].count;
                            if (currentCount <= 1) {
                                delete movieReactionState[existingEmoji];
                            } else {
                                movieReactionState[existingEmoji] = {
                                    count: currentCount - 1,
                                    userReacted: false
                                };
                            }
                        }
                    });
                }

                // Update the new reaction
                if (reactionData.count === 0) {
                    delete movieReactionState[emoji];
                } else {
                    movieReactionState[emoji] = {
                        count: reactionData.count,
                        userReacted: reactionData.user_reacted
                    };
                }

                if (Object.keys(movieReactionState).length === 0) {
                    movieReactions.delete(movieId);
                } else {
                    movieReactions.set(movieId, movieReactionState);
                }
            }

            function updateReactionUI(movieId) {
                // Update active reactions display
                const activeReactionsContainer = document.getElementById(`active-reactions-${movieId}`);
                const reactions = movieReactions.get(movieId) || {};

                activeReactionsContainer.innerHTML = '';

                Object.entries(reactions)
                    .filter(([_, data]) => data.count > 0)
                    .forEach(([emoji, data]) => {
                        const badge = document.createElement('span');
                        badge.className = `inline-flex items-center px-2 py-1 rounded-full text-sm
                ${data.userReacted ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200' :
                            'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'}`;
                        badge.innerHTML = `${emoji} ${data.count}`;
                        activeReactionsContainer.appendChild(badge);
                    });

                // Update modal buttons if modal is open
                if (currentMovieId === movieId) {
                    const buttons = document.querySelectorAll('#reactionButtons .reaction-btn');
                    buttons.forEach(button => {
                        const buttonEmoji = button.textContent.trim().split('\n')[0];
                        const reactionData = reactions[buttonEmoji] || { count: 0, userReacted: false };

                        button.classList.toggle('bg-gray-100', reactionData.userReacted);
                        button.classList.toggle('dark:bg-gray-700', reactionData.userReacted);
                        button.querySelector('span').textContent = reactionData.count;
                    });
                }
            }

            // Load initial reactions to display emojis
            async function loadInitialReactions() {
                try {
                    const response = await fetch(`/rooms/{{ $room->id }}/reactions`);
                    const reactions = await response.json();

                    // Clear existing reactions
                    movieReactions.clear();

                    // Group reactions by movie
                    reactions.forEach(reaction => {
                        const { movie_id, emoji, count, user_reacted } = reaction;
                        let movieReactionState = movieReactions.get(movie_id) || {};

                        movieReactionState[emoji] = {
                            count: count,
                            userReacted: user_reacted
                        };

                        movieReactions.set(movie_id, movieReactionState);
                        updateReactionUI(movie_id);
                    });
                } catch (error) {
                    console.error('Error loading reactions:', error);
                }
            }

            // Initialize reactions when page loads
            document.addEventListener('DOMContentLoaded', loadInitialReactions);

            // Close modal when clicking outside
            document.getElementById('reactionModal').addEventListener('click', (e) => {
                if (e.target === document.getElementById('reactionModal')) {
                    closeReactionModal();
                }
            });


        </script>
    @endpush
</x-app-layout>
