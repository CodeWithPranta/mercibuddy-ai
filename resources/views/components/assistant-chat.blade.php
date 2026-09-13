@php
    $initialMessages = session('assistant_chat', []);
@endphp
<div class="app-chat" id="yaara-chat" aria-label="Yaara Brains chat assistant"
    data-stream-url="{{ route('assistant.stream') }}"
    data-clear-url="{{ route('assistant.clear') }}">
    <header class="app-chat-header">
        <div class="app-chat-identity">
            <span class="app-chat-avatar" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m12 3 1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9L12 3Z" />
                    <path stroke-linecap="round" d="M19 15.5 19.8 17.7 22 18.5 19.8 19.3 19 21.5 18.2 19.3 16 18.5 18.2 17.7 19 15.5Z" />
                </svg>
            </span>
            <div>
                <p class="app-chat-title">Yaara Brains</p>
                <p class="app-chat-status" id="yaara-chat-status">Online</p>
            </div>
        </div>
        <div class="app-chat-actions">
            <button type="button" class="app-chat-action" id="yaara-chat-clear" aria-label="Clear chat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M4 7h16M9 7V5h6v2m-8 0 .8 13h8.4L17 7" /></svg>
            </button>
            <button type="button" class="app-chat-action" id="yaara-chat-close" aria-label="Close chat">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" /></svg>
            </button>
        </div>
    </header>

    <div class="app-chat-body" id="yaara-chat-body">
        @if (count($initialMessages) === 0)
            <div class="app-chat-message app-chat-message-bot" data-greeting>
                <p>Hi, I’m Yaara Brains. Please tell me what you need and I’ll help you find trusted skilled artisans near you. Example:</p>
                <div class="app-chat-suggestions">
                    <button type="button" data-suggest="I need a fitness instructor in Basel">I need a fitness instructor in Basel</button>
                    <button type="button" data-suggest="Find someone in Rennes to fix a leaking tap">Find someone in Rennes to fix a leaking tap</button>
                    <button type="button" data-suggest="Can you help me to find a Science tutor in my city?">Can you help me to find a Science tutor in my city?</button>
                </div>
            </div>
        @endif

        @foreach ($initialMessages as $message)
            <div class="app-chat-message {{ ($message['role'] ?? '') === 'user' ? 'app-chat-message-user' : 'app-chat-message-bot' }}">
                <p>{{ $message['text'] ?? '' }}</p>
                @if (! empty($message['artisans']))
                    <div class="app-chat-artisans">
                        @foreach ($message['artisans'] as $artisan)
                            <a href="{{ $artisan['profile_url'] ?? '#' }}" class="app-chat-artisan">
                                <span class="app-chat-artisan-top">
                                    @if (! empty($artisan['photo']))
                                        <img src="{{ $artisan['photo'] }}" alt="{{ $artisan['name'] ?? 'Artisan' }} photo" class="app-chat-artisan-photo" loading="lazy">
                                    @endif
                                    <span>
                                        <span class="app-chat-artisan-name">{{ $artisan['name'] ?? '' }}</span>
                                        <span class="app-chat-artisan-meta">{{ ($artisan['profession'] ?? '') ?: ($artisan['category'] ?? '') }} · {{ $artisan['experience_years'] ?? '' }}+ yrs</span>
                                    </span>
                                </span>
                                <span class="app-chat-artisan-location">{{ ($artisan['location'] ?? '') ?: 'See profile for location' }}</span>
                                <span class="app-chat-artisan-link">View profile</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <form class="app-chat-form" id="yaara-chat-form">
        <label for="yaara-chat-input" class="sr-only">Message Yaara Brains</label>
        <input
            id="yaara-chat-input"
            type="text"
            placeholder="Describe what you need.."
            class="app-chat-input"
            autocomplete="off"
            maxlength="500"
        >
        <p class="app-chat-error" id="yaara-chat-error" hidden></p>
        <button type="submit" class="app-chat-send" id="yaara-chat-send" aria-label="Send message">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="m4 12 16-8-6 16-2.5-6L4 12Z" /></svg>
        </button>
    </form>
</div>

<script>
(function () {
    var root = document.getElementById('yaara-chat');
    if (!root) {
        return;
    }

    var body = document.getElementById('yaara-chat-body');
    var form = document.getElementById('yaara-chat-form');
    var input = document.getElementById('yaara-chat-input');
    var sendBtn = document.getElementById('yaara-chat-send');
    var errorBox = document.getElementById('yaara-chat-error');
    var statusLine = document.getElementById('yaara-chat-status');
    var csrf = document.querySelector('meta[name="csrf-token"]');
    var busy = false;

    var HISTORY_KEY = 'yaara-chat-history';

    function loadHistory() {
        try {
            var parsed = JSON.parse(localStorage.getItem(HISTORY_KEY) || '[]');
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    function rememberMessage(role, text) {
        if (!text) {
            return;
        }

        var history = loadHistory();
        history.push({ role: role, text: text });

        if (history.length > 30) {
            history = history.slice(-30);
        }

        try {
            localStorage.setItem(HISTORY_KEY, JSON.stringify(history));
        } catch (e) {}
    }

    function forgetHistory() {
        try {
            localStorage.removeItem(HISTORY_KEY);
        } catch (e) {}
    }

    function textBubble(role, text) {
        var bubble = document.createElement('div');
        bubble.className = 'app-chat-message ' + (role === 'user' ? 'app-chat-message-user' : 'app-chat-message-bot');

        var paragraph = document.createElement('p');
        paragraph.textContent = text;
        bubble.appendChild(paragraph);
        body.appendChild(bubble);
    }

    // If the server session has no messages (expired session) but this
    // browser still holds a transcript backup, restore it so a refresh
    // never wipes the conversation.
    (function restoreBackup() {
        var serverMessages = body.querySelectorAll('.app-chat-message-user, .app-chat-message-bot:not([data-greeting])');

        if (serverMessages.length > 0) {
            var fresh = [];

            serverMessages.forEach(function (node) {
                var paragraph = node.querySelector('p');
                if (paragraph && paragraph.textContent) {
                    fresh.push({
                        role: node.classList.contains('app-chat-message-user') ? 'user' : 'assistant',
                        text: paragraph.textContent
                    });
                }
            });

            try {
                localStorage.setItem(HISTORY_KEY, JSON.stringify(fresh.slice(-30)));
            } catch (e) {}

            return;
        }

        loadHistory().forEach(function (entry) {
            if (entry && (entry.role === 'user' || entry.role === 'assistant') && entry.text) {
                textBubble(entry.role, entry.text);
            }
        });
    })();

    // Tapping a footer link navigates away: close the drawer first so it
    // never covers the next page and navigation always feels instant.
    // The More toggle is included so its menu never opens underneath
    // the open chat.
    document.querySelectorAll('.app-bottom-nav a, .app-bottom-nav button').forEach(function (link) {
        link.addEventListener('click', closeChat);
    });

    var SUGGESTIONS = [
        'I need a fitness instructor in Basel',
        'Find someone in Rennes to fix a leaking tap',
        'Can you help me to find a Science tutor in my city?'
    ];

    function openChat() {
        root.classList.add('app-chat-open');
        try {
            localStorage.setItem('yaara-open', '1');
        } catch (e) {}
    }

    function closeChat() {
        root.classList.remove('app-chat-open');
        try {
            localStorage.removeItem('yaara-open');
        } catch (e) {}
    }

    try {
        if (localStorage.getItem('yaara-open') === '1') {
            openChat();
        }
    } catch (e) {}

    window.addEventListener('open-assistant-chat', openChat);
    document.getElementById('yaara-chat-close').addEventListener('click', closeChat);

    // SPA (wire:navigate) navigations don't reload the page, so an open
    // drawer would survive them and cover the next page on mobile (e.g.
    // guest tapped a profile link and landed on login underneath the
    // chat). Close it on every real navigation. The flag guards the
    // initial page load so a remembered-open chat still restores.
    var navigationArmed = false;
    document.addEventListener('livewire:navigated', function () {
        if (navigationArmed) {
            closeChat();
        }
        navigationArmed = true;
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeChat();
        }
    });

    function scrollDown() {
        body.scrollTop = body.scrollHeight;
    }

    function hideError() {
        errorBox.hidden = true;
        errorBox.textContent = '';
    }

    function showError(text) {
        errorBox.hidden = false;
        errorBox.textContent = text;
    }

    function greetingBubble() {
        var bubble = document.createElement('div');
        bubble.className = 'app-chat-message app-chat-message-bot';
        bubble.setAttribute('data-greeting', '');

        var text = document.createElement('p');
        text.textContent = 'Hi, I’m Yaara Brains. Please tell me what you need and I’ll help you find trusted skilled artisans near you. Example:';
        bubble.appendChild(text);

        var suggestions = document.createElement('div');
        suggestions.className = 'app-chat-suggestions';

        SUGGESTIONS.forEach(function (suggestion) {
            var button = document.createElement('button');
            button.type = 'button';
            button.setAttribute('data-suggest', suggestion);
            button.textContent = suggestion;
            suggestions.appendChild(button);
        });

        bubble.appendChild(suggestions);

        return bubble;
    }

    function userBubble(text) {
        var bubble = document.createElement('div');
        bubble.className = 'app-chat-message app-chat-message-user';

        var paragraph = document.createElement('p');
        paragraph.textContent = text;
        bubble.appendChild(paragraph);
        body.appendChild(bubble);
        scrollDown();
    }

    function botShell() {
        var existing = body.querySelector('[data-greeting]');
        if (existing) {
            existing.remove();
        }

        var bubble = document.createElement('div');
        bubble.className = 'app-chat-message app-chat-message-bot';

        var paragraph = document.createElement('p');
        bubble.appendChild(paragraph);

        var typing = document.createElement('div');
        typing.className = 'app-chat-typing';
        typing.setAttribute('aria-hidden', 'true');
        typing.appendChild(document.createElement('span'));
        typing.appendChild(document.createElement('span'));
        typing.appendChild(document.createElement('span'));
        bubble.appendChild(typing);

        body.appendChild(bubble);
        scrollDown();

        return { bubble: bubble, text: paragraph, typing: typing };
    }

    function artisanCards(artisans) {
        var wrap = document.createElement('div');
        wrap.className = 'app-chat-artisans';

        (artisans || []).forEach(function (artisan) {
            var link = document.createElement('a');
            link.className = 'app-chat-artisan';
            link.setAttribute('href', artisan.profile_url || '#');
            // Close the drawer the moment a suggested profile is tapped so
            // it can never hide the page that opens (login on mobile takes
            // the whole screen otherwise).
            link.addEventListener('click', closeChat);

            var top = document.createElement('span');
            top.className = 'app-chat-artisan-top';

            if (artisan.photo) {
                var photo = document.createElement('img');
                photo.className = 'app-chat-artisan-photo';
                photo.setAttribute('src', artisan.photo);
                photo.setAttribute('alt', (artisan.name || 'Artisan') + ' photo');
                photo.setAttribute('loading', 'lazy');
                top.appendChild(photo);
            }

            var identity = document.createElement('span');
            var name = document.createElement('span');
            name.className = 'app-chat-artisan-name';
            name.textContent = artisan.name || '';
            identity.appendChild(name);

            var meta = document.createElement('span');
            meta.className = 'app-chat-artisan-meta';
            meta.textContent = (artisan.profession || artisan.category || '') + ' · ' + (artisan.experience_years || '') + '+ yrs';
            identity.appendChild(meta);
            top.appendChild(identity);

            var location = document.createElement('span');
            location.className = 'app-chat-artisan-location';
            location.textContent = artisan.location || 'See profile for location';

            var view = document.createElement('span');
            view.className = 'app-chat-artisan-link';
            view.textContent = 'View profile';

            link.appendChild(top);
            link.appendChild(location);
            link.appendChild(view);
            wrap.appendChild(link);
        });

        return wrap;
    }

    function handleEventBlock(block, shell, state) {
        var eventName = 'message';
        var payload = '';

        block.split('\n').forEach(function (line) {
            if (line.indexOf('event:') === 0) {
                eventName = line.slice(6).trim();
            } else if (line.indexOf('data:') === 0) {
                payload += line.slice(5).trim();
            }
        });

        if (eventName === 'token') {
            try {
                var token = JSON.parse(payload);
                if (!state.started) {
                    state.started = true;
                    shell.typing.remove();
                    statusLine.textContent = 'Typing...';
                }
                shell.text.textContent += token.text || '';
                scrollDown();
            } catch (e) {}
        } else if (eventName === 'done') {
            try {
                var done = JSON.parse(payload);
                state.finished = true;
                shell.text.textContent = done.message || shell.text.textContent;
                rememberMessage('assistant', shell.text.textContent);
                if (done.artisans && done.artisans.length) {
                    shell.bubble.appendChild(artisanCards(done.artisans));
                }
                scrollDown();
            } catch (e) {}
        }
    }

    function setBusy(value) {
        busy = value;
        input.disabled = value;
        sendBtn.disabled = value;
        statusLine.textContent = value ? 'Thinking...' : 'Online';
    }

    function submit(question) {
        var text = (typeof question === 'string' ? question : input.value).trim();

        if (busy || text.length < 2) {
            return;
        }

        hideError();
        setBusy(true);
        input.value = '';
        openChat();
        userBubble(text);
        rememberMessage('user', text);

        var shell = botShell();
        var state = { started: false, finished: false };

        fetch(root.dataset.streamUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'text/event-stream',
                'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : ''
            },
            body: JSON.stringify({ question: text })
        }).then(function (response) {
            var contentType = response.headers.get('content-type') || '';

            if (!response.ok || contentType.indexOf('text/event-stream') === -1) {
                return response.json().then(function (body) {
                    throw new Error(
                        (body && body.message) ||
                        (body && body.errors && body.errors.question && body.errors.question[0]) ||
                        'Request failed.'
                    );
                }).catch(function (error) {
                    if (error instanceof Error) {
                        throw error;
                    }
                    throw new Error('Request failed.');
                });
            }

            var reader = response.body.getReader();
            var decoder = new TextDecoder();
            var buffer = '';

            function pump() {
                return reader.read().then(function (result) {
                    if (result.done) {
                        return;
                    }

                    buffer += decoder.decode(result.value, { stream: true });

                    var boundary;
                    while ((boundary = buffer.indexOf('\n\n')) !== -1) {
                        var block = buffer.slice(0, boundary);
                        buffer = buffer.slice(boundary + 2);
                        handleEventBlock(block, shell, state);
                    }

                    return pump();
                });
            }

            return pump();
        }).then(function () {
            if (!state.finished && !state.started) {
                shell.typing.remove();
                shell.text.textContent = 'Sorry, I did not catch that. Please try again.';
            }
            if (!state.started) {
                shell.typing.remove();
            }
        }).catch(function (error) {
            shell.typing.remove();
            if (!shell.text.textContent) {
                shell.text.textContent = 'Sorry, something went wrong. Please try again.';
            }
            showError(error.message || 'Something went wrong.');
        }).finally(function () {
            setBusy(false);
            scrollDown();
        });
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        submit();
    });

    body.addEventListener('click', function (event) {
        var suggestion = event.target.closest('[data-suggest]');
        if (suggestion) {
            submit(suggestion.getAttribute('data-suggest'));
            return;
        }

        // Tapping a link inside the chat (e.g. an AI-suggested artisan
        // profile) must close the drawer first: the persisted `yaara-open`
        // flag would otherwise reopen it on top of the next page, which on
        // mobile covers the whole screen and makes the link look broken.
        var link = event.target.closest('a[href]');
        if (link) {
            closeChat();
        }
    });

    document.getElementById('yaara-chat-clear').addEventListener('click', function () {
        if (busy) {
            return;
        }

        fetch(root.dataset.clearUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : ''
            },
            body: '{}'
        }).finally(function () {
            body.innerHTML = '';
            body.appendChild(greetingBubble());
            forgetHistory();
            hideError();
            scrollDown();
        });
    });

    // Cards restored from the session are rendered by Blade rather than
    // built by artisanCards(): bind them directly as well.
    body.querySelectorAll('a.app-chat-artisan[href]').forEach(function (link) {
        link.addEventListener('click', closeChat);
    });

    scrollDown();
})();
</script>
