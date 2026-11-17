@php
    $filledStars = (int) round($average);
@endphp

<div>
    <div class="flex items-center justify-between gap-3">
        <h3 class="text-lg font-extrabold text-slate-900">Customer rating</h3>
        <span class="text-sm font-semibold text-slate-500">({{ number_format($total) }} {{ Str::plural('review', $total) }})</span>
    </div>

    @if ($total > 0)
        <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-center">
            <div class="flex items-center gap-3 sm:w-48 sm:shrink-0">
                <span class="text-4xl font-extrabold leading-none text-slate-900">{{ number_format($average, 1) }}</span>
                <div>
                    <div class="flex items-center gap-0.5 text-amber-400" aria-hidden="true">
                        @for ($star = 1; $star <= 5; $star++)
                            <svg class="h-4 w-4 {{ $star <= $filledStars ? 'fill-current' : 'fill-slate-200' }}" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L10 14.9l-5.2 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z" />
                            </svg>
                        @endfor
                    </div>
                    <p class="mt-1 text-xs font-semibold text-slate-600">{{ $label }}</p>
                </div>
            </div>

            <div class="flex-1 space-y-1">
                @foreach ([5, 4, 3, 2, 1] as $star)
                    <div class="flex items-center gap-2">
                        <span class="w-3 text-right text-[11px] font-bold text-slate-600">{{ $star }}</span>
                        <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-200">
                            <div class="h-full rounded-full bg-emerald-500" style="width: {{ $percentages[$star] }}%"></div>
                        </div>
                        <span class="w-8 text-right text-[11px] font-semibold text-slate-500">{{ $breakdown[$star] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <p class="mt-3 text-sm text-slate-600">No reviews yet. Be the first to review this artisan.</p>
    @endif

    @if (session('review_status'))
        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">
            {{ session('review_status') }}
        </div>
    @endif

    @auth
        @if ($isOwnProfile)
            <p class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                This is your profile. Customer reviews will appear here.
            </p>
        @elseif (! $showForm)
            <button type="button" wire:click="$set('showForm', true)" class="mt-4 inline-flex h-11 items-center justify-center rounded-xl border-2 border-emerald-700 bg-emerald-50 px-4 text-sm font-extrabold text-emerald-800 transition hover:bg-emerald-100">
                {{ $hasReviewed ? 'Edit your review' : 'Write a review' }}
            </button>
        @else
            <form wire:submit="submit" class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm font-bold text-slate-900">Your rating</p>
                <div class="mt-2 flex items-center gap-1">
                    @for ($star = 1; $star <= 5; $star++)
                        <button type="button" wire:click="$set('rating', {{ $star }})" class="rounded p-0.5 text-amber-400 transition hover:scale-110" aria-label="{{ $star }} star{{ $star > 1 ? 's' : '' }}" aria-pressed="{{ $rating >= $star ? 'true' : 'false' }}">
                            <svg class="h-7 w-7 {{ $star <= $rating ? 'fill-current' : 'fill-slate-300' }}" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L10 14.9l-5.2 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z" />
                            </svg>
                        </button>
                    @endfor
                </div>
                @error('rating')
                    <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror

                <label for="review-comment" class="mt-3 block text-sm font-bold text-slate-900">Your review</label>
                <textarea id="review-comment" wire:model="comment" rows="4" maxlength="1000" placeholder="Share your experience working with this artisan..." class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200"></textarea>
                @error('comment')
                    <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                @enderror

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-xl bg-emerald-700 px-5 text-sm font-extrabold text-white transition hover:bg-emerald-800" wire:loading.attr="disabled" wire:target="submit">
                        <span wire:loading.remove wire:target="submit">{{ $hasReviewed ? 'Update review' : 'Submit review' }}</span>
                        <span wire:loading wire:target="submit">Saving...</span>
                    </button>
                    <button type="button" wire:click="$set('showForm', false)" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-100">
                        Cancel
                    </button>
                </div>
            </form>
        @endif
    @else
        <p class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
            <a href="{{ route('login') }}" class="font-bold text-emerald-700 underline">Log in</a> to leave a review.
        </p>
    @endauth

    @if ($reviews->isNotEmpty())
        <div class="mt-5 space-y-3">
            @foreach ($reviews as $review)
                @php
                    $reviewStars = (int) ($review->rating ?? 0);
                @endphp
                <article class="rounded-xl border border-slate-200 p-3" wire:key="review-{{ $review->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-slate-900">{{ $review->user?->name ?? 'Customer' }}</p>
                            <p class="text-xs text-slate-500">{{ $review->created_at?->diffForHumans() }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-0.5 text-amber-400" aria-label="{{ $reviewStars }} out of 5 stars">
                            @for ($star = 1; $star <= 5; $star++)
                                <svg class="h-3.5 w-3.5 {{ $star <= $reviewStars ? 'fill-current' : 'fill-slate-200' }}" viewBox="0 0 20 20" aria-hidden="true">
                                    <path d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L10 14.9l-5.2 2.7 1-5.8L1.5 7.7l5.9-.9L10 1.5z" />
                                </svg>
                            @endfor
                        </div>
                    </div>
                    <p class="mt-2 whitespace-pre-line break-words text-sm text-slate-700 [overflow-wrap:anywhere]">{{ $review->comment }}</p>
                </article>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $reviews->links() }}
        </div>
    @endif
</div>
