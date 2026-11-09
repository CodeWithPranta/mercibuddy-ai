<footer x-data="{ open: false }" class="app-bottom-nav" aria-label="App navigation">
    <a href="/" wire:navigate.hover class="app-nav-item {{ request()->is('/') ? 'app-nav-active' : '' }}" aria-label="Home">
        <svg class="app-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10Z" /></svg>
    </a>
    @if ($showNotifications)
        <a href="{{ route('notifications.index') }}" wire:navigate.hover class="app-nav-item {{ request()->routeIs('notifications.index') ? 'app-nav-active' : '' }}" aria-label="Notifications">
            <span class="app-nav-icon-wrap">
                <svg class="app-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" /><path stroke-linecap="round" d="M10.3 21a1.94 1.94 0 0 0 3.4 0" /></svg>
                @if ($unreadQueryCount > 0)
                    <span class="app-nav-badge" aria-label="{{ $unreadQueryCount }} unread {{ Str::plural('query', $unreadQueryCount) }}">{{ $unreadQueryCount > 9 ? '9+' : $unreadQueryCount }}</span>
                @endif
            </span>
        </a>
    @else
        <a href="/page/about" wire:navigate.hover class="app-nav-item" aria-label="About">
            <svg class="app-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="8.5" /><path stroke-linecap="round" d="M12 11v5m0-8.5v.01" /></svg>
        </a>
    @endif
    <a href="{{ Auth::check() ? route('dashboard') : route('login') }}" wire:navigate.hover class="app-nav-item {{ request()->routeIs('dashboard') ? 'app-nav-active' : '' }}" aria-label="Account">
        <span class="app-nav-icon-wrap">
            <svg class="app-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="8" r="3.5" /><path stroke-linecap="round" d="M4.5 20c.7-3.4 3.2-5.5 7.5-5.5s6.8 2.1 7.5 5.5" /></svg>
            @auth
                <span class="app-nav-dot" title="Logged in as {{ Auth::user()->name }}" aria-label="Logged in"></span>
            @endauth
        </span>
    </a>
    <a href="/contact" wire:navigate.hover class="app-nav-item" aria-label="Messages">
        <svg class="app-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linejoin="round" d="M4 5.5h16v10H9l-4 3v-3H4v-10Z" /><path stroke-linecap="round" d="M8 10.5h.01m4 0h.01m4 0h.01" /></svg>
    </a>
    <button type="button" class="app-nav-item" aria-label="Menu" @click="open = ! open" :aria-expanded="open">
        <svg class="app-nav-icon app-menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" /></svg>
    </button>
    <div x-show="open" x-transition class="app-menu-panel" @click.outside="open = false">
        <a href="/page/terms-of-use" wire:navigate.hover>Terms of Use</a>
        <a href="/page/privacy-policy" wire:navigate.hover>Privacy Policy</a>
        @auth
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="app-menu-link">Log out ({{ Str::of(Auth::user()->name)->before(' ') }})</button>
            </form>
        @endauth
    </div>
</footer>
