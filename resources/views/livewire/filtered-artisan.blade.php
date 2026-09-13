<div class="app-page">
    <div class="app-page-inner">
        <nav class="app-crumb" aria-label="Breadcrumb">
            <a href="/" wire:navigate.hover>Home</a>
            <span aria-hidden="true">/</span>
            <span>{{ $country?->name }}</span>
            <span aria-hidden="true">/</span>
            <span class="app-crumb-current">{{ $category?->name }}</span>
        </nav>

        <header class="app-list-head">
            <div>
                <p class="app-eyebrow">{{ $country?->name }}</p>
                <h1 class="app-page-title">{{ $category?->name }}</h1>
                <p class="app-page-lead">
                    {{ $artisans->count() }} trusted {{ Str::plural('artisan', $artisans->count()) }} available
                    — ranked by community likes, experience and seniority.
                </p>
            </div>
        </header>

        <div class="app-list-controls">
            <form role="search" class="app-list-search">
                <label for="artisan-search" class="sr-only">Search artisans</label>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="m16.5 16.5 4 4" /></svg>
                <input wire:model.live.debounce.300ms="search" id="artisan-search" type="text" placeholder="Search by name, skill or area..." autocomplete="off">
            </form>
            <label class="app-list-sort">
                <span class="sr-only">Sort artisans</span>
                <select wire:model.live="sort" aria-label="Sort artisans">
                    @foreach ($this->sortOptions() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        </div>
        <div wire:loading class="app-loading">Searching...</div>

        @if ($artisans->count() !== 0)
            <div class="app-artisan-results">
                @foreach ($artisans as $index => $artisan)
                    @php
                        $profilePhoto = str_starts_with((string) $artisan->profile_photo, 'http')
                            ? $artisan->profile_photo
                            : asset('storage/'.$artisan->profile_photo);
                        $profileUrl = route('artisan.show', ['artisan' => $artisan->id, 'slug' => Str::slug($artisan->full_name)]);
                    @endphp
                    <article class="app-artisan-card">
                        <div class="app-artisan-card-head">
                            <a href="{{ $profileUrl }}" wire:navigate.hover tabindex="-1" aria-hidden="true">
                                <img src="{{ $profilePhoto }}" alt="{{ $artisan->full_name }} photo" class="app-artisan-photo" loading="lazy">
                            </a>
                            <div class="app-artisan-identity">
                                <h3>
                                    <a href="{{ $profileUrl }}" wire:navigate.hover>{{ Str::limit($artisan->full_name, 22) }}</a>
                                    @if ($index === 0 && $search === '' && $sort === 'recommended')
                                        <span class="app-top-badge">Top match</span>
                                    @endif
                                </h3>
                                <p>{{ $artisan->profession }}</p>
                                <small>{{ $artisan->locationLabel() }}</small>
                            </div>
                        </div>
                        <div class="app-artisan-chips">
                            <span class="app-chip">{{ $artisan->category?->name }}</span>
                            <span class="app-chip app-chip-gold">{{ $artisan->experienceYears() }}+ yrs</span>
                        </div>
                        <div class="app-artisan-rating">
                            <div class="app-artisan-rating-bar"><div style="width: {{ $artisan->satisfactionPercentage() }}%"></div></div>
                            <span>{{ $artisan->satisfactionPercentage() }}% · {{ $artisan->likeCount() }} {{ Str::plural('like', $artisan->likeCount()) }}</span>
                        </div>
                        <a href="{{ $profileUrl }}" wire:navigate.hover class="app-artisan-link">View profile</a>
                    </article>
                @endforeach
            </div>
        @else
            <div class="app-empty-state">
                <span aria-hidden="true">Oops!</span>
                <p>No {{ strtolower($category?->name ?? 'artisan') }} available in {{ $country?->name }} at the moment.</p>
                <a href="/" wire:navigate.hover class="app-artisan-link">Try another country or craft</a>
            </div>
        @endif
    </div>
</div>
