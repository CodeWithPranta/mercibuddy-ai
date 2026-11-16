<nav class="app-topbar" aria-label="Primary navigation">
    <div class="app-topbar-row">
        <a href="/" wire:navigate.hover class="app-brand">
            <x-custom-logo />
        </a>
        <button type="button" class="app-ai-trigger" wire:click="$dispatch('open-assistant-chat')" aria-label="Open AI assistant chat">
            <span class="app-ai-trigger-pill">AI</span>
            <span class="app-ai-trigger-label">Ask AI</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.5 4.5a3 3 0 0 0-5.5 1.7c0 .55.15 1.06.4 1.5A3.5 3.5 0 0 0 5.5 14a3 3 0 0 0 3 4.5h.5V4.5Zm5 0a3 3 0 0 1 5.5 1.7c0 .55-.15 1.06-.4 1.5a3.5 3.5 0 0 1-1.1 6.3 3 3 0 0 1-3 4.5h-.5V4.5ZM12 4v16M8 8h2m-3 4h3m6-4h-2m3 4h-3" />
            </svg>
        </button>
    </div>
</nav>
