<div class="app-react">
    <button type="button" wire:click="like" class="app-react-btn {{ auth()->user()?->likedArtisans?->contains($artisan->id) ? 'app-react-active' : '' }}" aria-label="Like this artisan" aria-pressed="{{ auth()->user()?->likedArtisans?->contains($artisan->id) ? 'true' : 'false' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 11v9H4a1 1 0 0 1-1-1v-7a1 1 0 0 1 1-1h3Zm2.5-.3 1-4.6c.1-.5.5-.9 1-.9.7 0 1.2.6 1 1.3l-.6 2.7H19c.8 0 1.4.8 1.2 1.6l-1.4 5.2c-.2.7-.8 1-1.5 1H9.5v-6.3Z" /></svg>
        <span>{{ $artisan->likeCount() }}</span>
    </button>
    <button type="button" wire:click="dislike" class="app-react-btn {{ auth()->user()?->dislikedArtisans?->contains($artisan->id) ? 'app-react-active' : '' }}" aria-label="Dislike this artisan" aria-pressed="{{ auth()->user()?->dislikedArtisans?->contains($artisan->id) ? 'true' : 'false' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 13V4h3a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1h-3Zm-2.5.3-1 4.6c-.1.5-.5.9-1 .9-.7 0-1.2-.6-1-1.3l.6-2.7H5c-.8 0-1.4-.8-1.2-1.6l1.4-5.2c.2-.7.8-1 1.5-1h7.8v6.3Z" /></svg>
        <span>{{ $artisan->dislikeCount() }}</span>
    </button>
    @if ($artisan->website)
        <a href="{{ $artisan->website }}" target="_blank" rel="noopener" class="app-react-btn" aria-label="Visit website">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="12" r="8.5" /><path stroke-linecap="round" d="M3.5 12h17M12 3.5c2.3 2.4 3.4 5.3 3.4 8.5s-1.1 6.1-3.4 8.5c-2.3-2.4-3.4-5.3-3.4-8.5S9.7 5.9 12 3.5Z" /></svg>
        </a>
    @endif
    @if ($artisan->video_cv)
        <a href="{{ $artisan->video_cv }}" target="_blank" rel="noopener" class="app-react-btn" aria-label="Watch video CV">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="3" /><path stroke-linecap="round" stroke-linejoin="round" d="m10.5 9.7 4.5 2.3-4.5 2.3V9.7Z" /></svg>
        </a>
    @endif
</div>
