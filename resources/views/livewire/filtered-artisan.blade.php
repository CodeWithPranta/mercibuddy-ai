<div class="app-page">
    <div class="app-page-inner">
        <nav class="app-crumb" aria-label="Breadcrumb">
            <a href="/" wire:navigate.hover>Home</a>
            <span aria-hidden="true">/</span>
            <span>{{ $country?->name }}</span>
            <span aria-hidden="true">/</span>
            <span class="app-crumb-current">{{ $category?->name }}</span>
        </nav>

        <header class="app-results-head">
            <p class="app-eyebrow">{{ $country?->name }}</p>
            <h1 class="app-page-title">{{ $category?->name }}</h1>
            <p class="app-results-count">
                <strong>{{ $artisans->count() }}</strong>
                {{ Str::plural('artisan', $artisans->count()) }} available — ranked by community likes, experience and seniority
            </p>
        </header>

        <div class="app-searchbar">
            <form role="search" class="app-searchbar-field">
                <label for="artisan-search" class="sr-only">Search artisans</label>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="m16.5 16.5 4 4" /></svg>
                <input wire:model.live.debounce.300ms="search" id="artisan-search" type="text" placeholder="Search by name, skill or area..." autocomplete="off">
            </form>
            <label class="app-searchbar-sort">
                <select wire:model.live="sort" aria-label="Sort artisans" class="!pl-3">
                    @foreach ($this->sortOptions() as $value => $label)
                        <option value="{{ $value }}">Sort by: {{ $label }}</option>
                    @endforeach
                </select>
            </label>
        </div>
        <div wire:loading class="app-loading">Searching...</div>

        @if ($artisans->count() !== 0)
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-2">
                @foreach ($artisans as $artisan)
                    @php
                        $profilePhoto = str_starts_with((string) $artisan->profile_photo, 'http')
                            ? $artisan->profile_photo
                            : asset('storage/'.$artisan->profile_photo);
                        $profileUrl = route('artisan.show', ['artisan' => $artisan->id, 'slug' => Str::slug($artisan->full_name)]);
                        $skillLines = array_values(array_filter([
                            $artisan->profession,
                            $artisan->profession_type,
                            $artisan->category?->name,
                            $artisan->last_education,
                        ]));
                        $fallbackSkills = ['Microsoft Office and WordPress', 'Research and Analytical Skills', 'Critical thinking', 'AI & Adaptability Trends'];
                        foreach ($fallbackSkills as $skill) {
                            if (count($skillLines) >= 4) break;
                            if (! in_array($skill, $skillLines, true)) $skillLines[] = $skill;
                        }
                        $skillLines = array_slice($skillLines, 0, 4);
                    @endphp
                    <article class="relative flex min-h-[190px] flex-col rounded-2xl border border-slate-200 bg-[#bdf3fb] p-3 shadow-sm transition hover:shadow-md sm:p-4">
                        <div class="grid flex-1 gap-3 pt-2 md:grid-cols-[minmax(8rem,0.8fr)_minmax(0,1.5fr)] md:items-start">
                            <div class="flex flex-col items-start">
                                <span class="mb-2 inline-flex h-4 w-9 items-center rounded-full bg-slate-300" title="{{ $artisan->is_active ? 'Active' : 'Out of service' }}" aria-label="{{ $artisan->is_active ? 'Active' : 'Out of service' }}" role="status">
                                    <span class="h-4 w-4 rounded-full shadow-sm {{ $artisan->is_active ? 'ml-auto bg-emerald-600' : 'bg-slate-500' }}"></span>
                                </span>
                                <a href="{{ $profileUrl }}" wire:navigate.hover class="block">
                                    <div class="relative h-20 w-20 shrink-0 overflow-hidden rounded-full border-2 border-white bg-slate-200 shadow-sm">
                                        <svg viewBox="0 0 40 40" class="h-full w-full" aria-hidden="true">
                                            <rect width="40" height="40" fill="#3b4a5a"/>
                                            <circle cx="20" cy="15" r="8" fill="#d8a884"/>
                                            <path d="M4 40 C4 26 36 26 36 40 Z" fill="#e2b98f"/>
                                        </svg>
                                        <img src="{{ $profilePhoto }}" alt="{{ $artisan->full_name }} photo" class="absolute inset-0 h-full w-full object-cover" loading="lazy" onerror="this.style.display='none'">
                                        <span class="absolute bottom-0 right-0 flex h-5 w-5 items-center justify-center gap-0.5 rounded-full border-2 border-white bg-rose-500" aria-hidden="true">
                                            <span class="h-0.5 w-0.5 rounded-full bg-white"></span>
                                            <span class="h-0.5 w-0.5 rounded-full bg-white"></span>
                                            <span class="h-0.5 w-0.5 rounded-full bg-white"></span>
                                        </span>
                                    </div>
                                </a>
                            </div>

                            <a href="{{ $profileUrl }}" wire:navigate.hover class="block">
                                <div class="rounded-2xl border-2 border-slate-300 bg-slate-200 px-3 py-2">
                                    @foreach ($skillLines as $skill)
                                        <p class="text-[12px] font-medium leading-[1.5] text-slate-700">{{ $skill }}</p>
                                    @endforeach
                                </div>
                            </a>
                        </div>

                        <div class="mt-2 grid grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] items-end gap-2">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold leading-tight text-slate-900">{{ $artisan->full_name }}</p>
                                <p class="mt-1 flex items-center gap-1 truncate text-xs text-slate-700">
                                    <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5Z"/></svg>
                                    {{ $artisan->city?->name ?? $artisan->locationLabel() }}
                                </p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold leading-none text-slate-900">{{ $artisan->satisfactionPercentage() }}%</p>
                                <p class="mt-1 whitespace-nowrap text-xs text-slate-700">satisfaction rate</p>
                            </div>
                            <span class="justify-self-end whitespace-nowrap rounded-full bg-[#87382d] px-3 py-2 text-xs font-bold text-white">
                                @if ($artisan->hourly_rate !== null)
                                    {{ $artisan->currency }} {{ rtrim(rtrim(number_format((float) $artisan->hourly_rate, 2, '.', ''), '0'), '.') }}/hour
                                @else
                                    Contact for rate
                                @endif
                            </span>
                        </div>
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
