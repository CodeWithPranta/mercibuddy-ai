@extends('layouts.landing')
@section('surface_class', 'surface-grey')
@section('content')

<livewire:navigation />

@php
    $profilePhoto = str_starts_with((string) $artisan->profile_photo, 'http')
        ? $artisan->profile_photo
        : asset('storage/'.$artisan->profile_photo);
    $coverPhoto = str_starts_with((string) $artisan->cover_photo, 'http')
        ? $artisan->cover_photo
        : asset('storage/'.$artisan->cover_photo);
    $age = (new DateTime($artisan->date_of_birth))->diff(new DateTime())->y;
    $listingUrl = $artisan->country && $artisan->category && $artisan->country->slug && $artisan->category->slug
        ? route('artisans.index', ['country' => $artisan->country->slug, 'category' => $artisan->category->slug])
        : '/';
    $profileUrl = route('artisan.show', ['artisan' => $artisan->id, 'slug' => Str::slug($artisan->full_name)]);
    $callHref = $media?->phone ? 'tel:'.$media->phone : null;
    $messageHref = $media?->email ? 'mailto:'.$media->email : ($media?->whatsapp ? 'https://wa.me/'.$media->whatsapp : null);
    $languageSummary = collect($artisan->language_proficiencies ?? [])
        ->filter(fn (array $language): bool => filled($language['language'] ?? null))
        ->map(fn (array $language): string => $language['language'].(! empty($language['proficiency']) ? ' ('.$language['proficiency'].')' : ''))
        ->implode(', ');
@endphp

<div class="app-page">
    <div class="app-page-inner">
        <nav class="app-crumb" aria-label="Breadcrumb">
            <a href="/" wire:navigate.hover>Home</a>
            <span aria-hidden="true">/</span>
            <a href="{{ $listingUrl }}" wire:navigate.hover>{{ $artisan->category?->name }}</a>
            <span aria-hidden="true">/</span>
            <span class="app-crumb-current">{{ Str::limit($artisan->full_name, 24) }}</span>
        </nav>

        <div class="mx-auto w-full max-w-4xl">
            <div class="space-y-7 lg:grid lg:grid-cols-2 lg:gap-7 lg:space-y-0">

                <!-- Profile card -->
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
                    <div class="relative h-28 w-full overflow-hidden">
                        <svg viewBox="0 0 400 140" preserveAspectRatio="xMidYMid slice" class="h-full w-full" aria-hidden="true">
                            <defs>
                                <linearGradient id="sky" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0%" stop-color="#c9d6df"/>
                                    <stop offset="100%" stop-color="#eef2f3"/>
                                </linearGradient>
                            </defs>
                            <rect width="400" height="140" fill="url(#sky)"/>
                            <rect x="30" y="10" width="60" height="130" fill="#9fb0b8"/>
                            <rect x="100" y="35" width="45" height="105" fill="#b7c4cc"/>
                            <rect x="155" y="0" width="70" height="140" fill="#8fa2ab"/>
                            <g transform="translate(230,55)">
                                <rect x="0" y="10" width="130" height="34" rx="14" fill="#1e2b3a"/>
                                <circle cx="120" cy="27" r="16" fill="#d8a884"/>
                                <rect x="20" y="18" width="70" height="18" rx="9" fill="#d8a884"/>
                                <rect x="15" y="5" width="20" height="45" fill="#f0a93a"/>
                                <rect x="35" y="5" width="20" height="45" fill="#2f6fb0"/>
                            </g>
                        </svg>
                        <img src="{{ $coverPhoto }}" alt="" class="absolute inset-0 h-full w-full object-cover" loading="lazy" onerror="this.style.display='none'">
                    </div>

                    <div class="px-4 pt-3 pb-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="flex min-w-0 flex-1 items-center gap-3">
                                <div class="relative -mt-8 h-11 w-11 shrink-0 overflow-hidden rounded-full border-2 border-white bg-slate-200">
                                    <svg viewBox="0 0 40 40" class="h-full w-full" aria-hidden="true">
                                        <rect width="40" height="40" fill="#3b4a5a"/>
                                        <circle cx="20" cy="15" r="8" fill="#d8a884"/>
                                        <path d="M4 40 C4 26 36 26 36 40 Z" fill="#e2b98f"/>
                                    </svg>
                                    <img src="{{ $profilePhoto }}" alt="{{ $artisan->full_name }} photo" class="absolute inset-0 h-full w-full object-cover" loading="lazy" onerror="this.style.display='none'">
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold leading-tight text-slate-900">{{ $artisan->full_name }} ({{ $age }} years)</p>
                                    <p class="truncate text-xs text-slate-500">{{ $artisan->locationLabel() }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <x-share-menu :url="$profileUrl" :title="$artisan->full_name" class="h-9 w-9" />
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-2">
                            <span class="text-[11px] font-semibold text-slate-700">{{ $artisan->satisfactionPercentage() }}%</span>
                            <div class="h-1.5 min-w-[6rem] flex-1 overflow-hidden rounded-full bg-slate-200">
                                <div class="h-full bg-fuchsia-400" style="width: {{ $artisan->satisfactionPercentage() }}%"></div>
                            </div>
                            <span class="whitespace-nowrap text-[11px] text-slate-500">Highly rated</span>
                            <span class="ml-auto whitespace-nowrap rounded-full bg-orange-400 px-3 py-1.5 text-xs font-bold text-white">
                                @if ($artisan->hourly_rate !== null)
                                    {{ $artisan->currency }} {{ rtrim(rtrim(number_format((float) $artisan->hourly_rate, 2, '.', ''), '0'), '.') }}/hour
                                @else
                                    Contact for rate
                                @endif
                            </span>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                            <p class="text-[11px] text-slate-400">Joined {{ $artisan->created_at?->format('F Y') }}</p>
                            <livewire:like-dislike :artisan="$artisan" />
                        </div>
                    </div>
                </div>

                <!-- Let's Connect -->
                <div id="connect" class="rounded-2xl border border-slate-200 bg-[#bdf3fb] p-4 lg:col-span-2">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h2 class="text-lg font-bold text-slate-900">Let&rsquo;s Connect</h2>
                            <p class="mt-1 max-w-[20rem] text-xs leading-snug text-slate-700">
                                Speaks: {{ $languageSummary !== '' ? $languageSummary : 'Languages not provided' }}
                            </p>
                        </div>

                        <div class="flex w-full items-center gap-4 sm:w-auto sm:justify-end">
                            @if ($callHref)
                                <a href="{{ $callHref }}" class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500 text-white shadow-sm transition-colors hover:bg-emerald-600" aria-label="Call">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                </a>
                            @else
                                <button type="button" class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500 text-white shadow-sm" aria-label="Call">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                </button>
                            @endif

                            @if ($messageHref)
                                <a href="{{ $messageHref }}" @if (str_starts_with($messageHref, 'http')) target="_blank" rel="noopener" @endif class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-500 text-white shadow-sm transition-colors hover:bg-blue-600" aria-label="Message">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/></svg>
                                </a>
                            @else
                                <button type="button" class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-500 text-white shadow-sm" aria-label="Message">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/></svg>
                                </button>
                            @endif

                            <button type="button" class="flex h-12 w-12 items-center justify-center rounded-full bg-rose-600 text-white shadow-sm transition-colors hover:bg-rose-700" aria-label="Notifications">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Personal statement -->
                <div class="rounded-2xl border border-slate-200 bg-[#bdf3fb] px-4 py-4">
                    <div class="flex items-center justify-between">
                        <span class="text-[15px] font-semibold text-slate-900">Personal Statement</span>
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-900 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        </span>
                    </div>
                    @if ($artisan->biography)
                        <div class="mt-3 text-sm leading-relaxed text-slate-600">{!! $artisan->biography !!}</div>
                    @endif
                </div>

                <!-- Service details -->
                <div class="rounded-2xl border border-slate-200 bg-[#bdf3fb] p-4">
                    <h3 class="mb-3 text-lg font-bold text-slate-900">Service Details</h3>

                    @forelse ($services as $service)
                        @php
                            $featuredImage = str_starts_with((string) $service->featured_image, 'http')
                                ? $service->featured_image
                                : asset('storage/'.$service->featured_image);
                            $serviceSummary = Str::limit(trim(strip_tags((string) $service->content)), 90);
                        @endphp
                        <div class="relative mb-3 overflow-hidden rounded-lg border border-slate-300">
                            <svg viewBox="0 0 400 140" preserveAspectRatio="xMidYMid slice" class="h-32 w-full bg-slate-100" aria-hidden="true">
                                <rect width="400" height="140" fill="#dbe7ea"/>
                                <rect x="0" y="90" width="400" height="50" fill="#2b3a55"/>
                                <circle cx="70" cy="55" r="20" fill="#e2b98f"/>
                                <rect x="45" y="75" width="50" height="45" fill="#ffffff"/>
                                <rect x="45" y="30" width="14" height="45" fill="#e2b98f"/>
                                <circle cx="200" cy="50" r="20" fill="#8a5a3a"/>
                                <rect x="175" y="70" width="50" height="45" fill="#334155"/>
                                <rect x="215" y="25" width="14" height="45" fill="#8a5a3a"/>
                                <circle cx="320" cy="55" r="20" fill="#c98a5b"/>
                                <rect x="295" y="75" width="50" height="45" fill="#e2e8f0"/>
                                <rect x="335" y="30" width="14" height="45" fill="#c98a5b"/>
                            </svg>
                            @if ($featuredImage)
                                <img src="{{ $featuredImage }}" alt="{{ $service->title }}" class="absolute inset-0 h-32 w-full object-cover" loading="lazy" onerror="this.style.display='none'">
                            @endif
                        </div>
                        <div class="mb-1 flex items-center gap-2">
                            <span class="font-bold text-slate-900">{{ $service->title }}</span>
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-900 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                            </span>
                        </div>
                        <p class="mb-3 text-sm text-slate-600">{{ $serviceSummary }}</p>
                    @empty
                        <div class="mb-3 overflow-hidden rounded-lg border border-slate-300">
                            <svg viewBox="0 0 400 140" preserveAspectRatio="xMidYMid slice" class="h-32 w-full bg-slate-100" aria-hidden="true">
                                <rect width="400" height="140" fill="#dbe7ea"/>
                                <rect x="0" y="90" width="400" height="50" fill="#2b3a55"/>
                                <circle cx="70" cy="55" r="20" fill="#e2b98f"/>
                                <rect x="45" y="75" width="50" height="45" fill="#ffffff"/>
                                <rect x="45" y="30" width="14" height="45" fill="#e2b98f"/>
                                <circle cx="200" cy="50" r="20" fill="#8a5a3a"/>
                                <rect x="175" y="70" width="50" height="45" fill="#334155"/>
                                <rect x="215" y="25" width="14" height="45" fill="#8a5a3a"/>
                                <circle cx="320" cy="55" r="20" fill="#c98a5b"/>
                                <rect x="295" y="75" width="50" height="45" fill="#e2e8f0"/>
                                <rect x="335" y="30" width="14" height="45" fill="#c98a5b"/>
                            </svg>
                        </div>
                        <div class="mb-1 flex items-center gap-2">
                            <span class="font-bold text-slate-900">Highlight title</span>
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-900 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                            </span>
                        </div>
                        <p class="text-sm text-slate-600">Add a short description that highlights this feature, value.</p>
                    @endforelse
                </div>

            </div>
        </div>
    </div>
</div>

<livewire:footer-section />
@endsection
