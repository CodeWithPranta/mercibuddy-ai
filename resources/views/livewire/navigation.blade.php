<nav class="app-topbar" aria-label="Primary navigation">
    <div class="app-topbar-row">
        <a href="/" wire:navigate.hover class="app-brand">
            @if ($setting?->logo)
                @php
                    $legacyLogoPath = public_path('photos/'.$setting->logo);
                    $logoUrl = file_exists($legacyLogoPath)
                        ? asset('photos/'.$setting->logo)
                        : \Illuminate\Support\Facades\Storage::disk('public')->url($setting->logo);
                @endphp
                <x-custom-logo src="{{ $logoUrl }}" class="app-brand-logo" />
            @else
                merci<span>buddy</span>
            @endif
        </a>
        <button type="button" class="app-ai-trigger" wire:click="$dispatch('open-assistant-chat')" aria-label="Open AI assistant chat">
            <span class="app-ai-trigger-pill">AI</span>
            <span class="app-ai-trigger-label">Ask AI</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="m12 3 1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9L12 3Z" />
                <path stroke-linecap="round" d="M19 15.5 19.8 17.7 22 18.5 19.8 19.3 19 21.5 18.2 19.3 16 18.5 18.2 17.7 19 15.5Z" />
            </svg>
        </button>
    </div>
</nav>
