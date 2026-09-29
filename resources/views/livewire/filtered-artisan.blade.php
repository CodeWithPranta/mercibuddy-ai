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
                <span>Sort by:</span>
                <select wire:model.live="sort" aria-label="Sort artisans">
                    @foreach ($this->sortOptions() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        </div>
        <div wire:loading class="app-loading">Searching...</div>

        @if ($artisans->count() !== 0)
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($artisans as $artisan)
                    @php
                        $profilePhoto = str_starts_with((string) $artisan->profile_photo, 'http')
                            ? $artisan->profile_photo
                            : asset('storage/'.$artisan->profile_photo);
                        $profileUrl = route('artisan.show', ['artisan' => $artisan->id, 'slug' => Str::slug($artisan->full_name)]);
                        $messageUrl = $artisan->contactDetail?->email
                            ? 'mailto:'.$artisan->contactDetail->email
                            : ($artisan->contactDetail?->whatsapp ? 'https://wa.me/'.$artisan->contactDetail->whatsapp : $profileUrl.'#connect');
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
                    <article class="relative flex flex-col rounded-2xl border border-slate-200 bg-[#bdf3fb] p-3 shadow-sm transition hover:shadow-md sm:p-4">
                        <span class="absolute left-3 top-3 h-3 w-3 rounded-full border-2 border-white bg-emerald-500" aria-hidden="true"></span>

                        <a href="{{ $profileUrl }}" wire:navigate.hover class="block">
                            <div class="mb-3 ml-8 rounded-xl border border-slate-200 bg-white/80 px-3 py-2">
                                @foreach ($skillLines as $skill)
                                    <p class="text-[12px] font-semibold leading-snug text-slate-800">{{ $skill }}</p>
                                @endforeach
                            </div>

                            <div class="flex items-center gap-2">
                                <div class="flex min-w-0 flex-1 items-center gap-2">
                                    <div class="relative h-10 w-10 shrink-0 overflow-hidden rounded-full bg-slate-200 sm:h-11 sm:w-11">
                                        <svg viewBox="0 0 40 40" class="h-full w-full" aria-hidden="true">
                                            <rect width="40" height="40" fill="#3b4a5a"/>
                                            <circle cx="20" cy="15" r="8" fill="#d8a884"/>
                                            <path d="M4 40 C4 26 36 26 36 40 Z" fill="#e2b98f"/>
                                        </svg>
                                        <img src="{{ $profilePhoto }}" alt="{{ $artisan->full_name }} photo" class="absolute inset-0 h-full w-full object-cover" loading="lazy" onerror="this.style.display='none'">
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold leading-tight text-slate-900">{{ $artisan->full_name }}</p>
                                        <p class="truncate text-xs text-slate-500">{{ $artisan->locationLabel() }}</p>
                                    </div>
                                </div>

                                <div class="shrink-0 px-1 text-center">
                                    <p class="text-lg font-bold leading-none text-slate-900">{{ $artisan->satisfactionPercentage() }}%</p>
                                    <p class="whitespace-nowrap text-[10px] text-slate-500">satisfaction rate</p>
                                </div>

                                <span class="shrink-0 whitespace-nowrap rounded-full bg-orange-400 px-2.5 py-1.5 text-[11px] font-bold text-white sm:px-3 sm:text-xs">
                                    @if ($artisan->hourly_rate !== null)
                                        {{ $artisan->currency }} {{ rtrim(rtrim(number_format((float) $artisan->hourly_rate, 2, '.', ''), '0'), '.') }}/hour
                                    @else
                                        Contact for rate
                                    @endif
                                </span>
                            </div>
                        </a>

                        <div class="mt-3 flex items-center justify-between gap-2 border-t border-slate-900/10 pt-3">
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-slate-700">
                                <svg class="h-3.5 w-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 11v9H4a1 1 0 0 1-1-1v-7a1 1 0 0 1 1-1h3Zm2.5-.3 1-4.6c.1-.5.5-.9 1-.9.7 0 1.2.6 1 1.3l-.6 2.7H19c.8 0 1.4.8 1.2 1.6l-1.4 5.2c-.2.7-.8 1-1.5 1H9.5v-6.3Z"/></svg>
                                {{ $artisan->likeCount() }} likes
                            </span>
                            <div class="flex items-center gap-2">
                                <a href="{{ $messageUrl }}" @if (str_starts_with($messageUrl, 'http')) target="_blank" rel="noopener" @endif class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-blue-500 text-white transition hover:bg-blue-600" aria-label="Message {{ $artisan->full_name }}">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path stroke-linecap="round" d="m3 7 9 6 9-6"/></svg>
                                </a>
                                <x-share-menu :url="$profileUrl" :title="$artisan->full_name" class="h-9 w-9" />
                            </div>
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
