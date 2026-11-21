@php
    $liked = auth()->user()?->likedArtisans?->contains($artisan->id) ?? false;
    $disliked = auth()->user()?->dislikedArtisans?->contains($artisan->id) ?? false;
@endphp

<div class="grid w-full grid-cols-2 gap-3">
    <button type="button" wire:click="like" class="inline-flex h-12 items-center justify-center gap-2 rounded-xl border-2 text-base font-extrabold transition {{ $liked ? 'border-blue-900 bg-blue-900 text-white' : 'border-blue-800 bg-blue-100 text-blue-900 hover:bg-blue-200' }}" aria-label="Like this artisan" aria-pressed="{{ $liked ? 'true' : 'false' }}">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 11v9H4a1 1 0 0 1-1-1v-7a1 1 0 0 1 1-1h3Zm2.5-.3 1-4.6c.1-.5.5-.9 1-.9.7 0 1.2.6 1 1.3l-.6 2.7H19c.8 0 1.4.8 1.2 1.6l-1.4 5.2c-.2.7-.8 1-1.5 1H9.5v-6.3Z" /></svg>
        <span>Like</span>
        <span class="tabular-nums">{{ number_format($artisan->likeCount()) }}</span>
    </button>
    <button type="button" wire:click="dislike" class="inline-flex h-12 items-center justify-center gap-2 rounded-xl border-2 text-base font-extrabold transition {{ $disliked ? 'border-blue-900 bg-blue-900 text-white' : 'border-blue-800 bg-white text-blue-900 hover:bg-blue-100' }}" aria-label="Dislike this artisan" aria-pressed="{{ $disliked ? 'true' : 'false' }}">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 13V4h3a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1h-3Zm-2.5.3-1 4.6c-.1.5-.5.9-1 .9-.7 0-1.2-.6-1-1.3l.6-2.7H5c-.8 0-1.4-.8-1.2-1.6l1.4-5.2c.2-.7.8-1 1.5-1h7.8v6.3Z" /></svg>
        <span>Dislike</span>
        <span class="tabular-nums">{{ number_format($artisan->dislikeCount()) }}</span>
    </button>
</div>
