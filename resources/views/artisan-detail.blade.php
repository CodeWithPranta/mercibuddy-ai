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
    $profileUrl = route('artisan.show', ['artisan' => $artisan->id, 'slug' => Str::slug($artisan->full_name)]);
    $callHref = $media?->phone ? 'tel:'.$media->phone : null;
    $messageHref = $media?->email ? 'mailto:'.$media->email : null;
    $videoCv = str_starts_with((string) $artisan->video_cv, 'http')
        ? $artisan->video_cv
        : asset('storage/'.$artisan->video_cv);
    $languageSummary = collect($artisan->language_proficiencies ?? [])
        ->filter(fn (array $language): bool => filled($language['language'] ?? null))
        ->sortBy(fn (array $language): string => mb_strtolower($language['language']))
        ->map(fn (array $language): string => $language['language'].(! empty($language['proficiency']) ? ' ('.$language['proficiency'].')' : ''))
        ->implode(', ');
    $age = filled($artisan->date_of_birth) ? \Illuminate\Support\Carbon::parse($artisan->date_of_birth)->age : null;
    $profession = $artisan->main_profession;
    $address = collect([$artisan->city?->name, $artisan->state?->name, $artisan->country?->name])
        ->filter()
        ->implode(', ');
    $hasServiceAttachment = $services->contains(fn ($service): bool => filled($service->featured_image));
    $satisfaction = (int) $artisan->satisfactionPercentage();
    $ratingLabel = $satisfaction >= 80 ? 'Excellent' : ($satisfaction >= 60 ? 'Good' : ($satisfaction >= 40 ? 'Average' : 'Unrated'));
    $ratingOptions = ['Excellent', 'Good', 'Average', 'Unrated'];
@endphp

<div class="app-page">
    <div class="app-page-inner">
        <div class="mx-auto w-full max-w-5xl">
            <div class="space-y-7 lg:grid lg:grid-cols-2 lg:gap-7 lg:space-y-0">

                <!-- Profile card -->
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
                    <div class="relative aspect-[16/7] w-full overflow-hidden rounded-t-2xl sm:aspect-[16/6]">
                        <svg viewBox="0 0 400 140" preserveAspectRatio="xMidYMid slice" class="h-full w-full" aria-hidden="true">
                            <defs>
                                <linearGradient id="sky" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0%" stop-color="#e2e8f0"/>
                                    <stop offset="100%" stop-color="#f8fafc"/>
                                </linearGradient>
                            </defs>
                            <rect width="400" height="140" fill="url(#sky)"/>
                            <rect x="30" y="10" width="60" height="130" fill="#cbd5e1"/>
                            <rect x="100" y="35" width="45" height="105" fill="#dbe3ea"/>
                            <rect x="155" y="0" width="70" height="140" fill="#c2ccd6"/>
                            <g transform="translate(230,55)">
                                <rect x="0" y="10" width="130" height="34" rx="14" fill="#334155"/>
                                <circle cx="120" cy="27" r="16" fill="#e2e8f0"/>
                                <rect x="20" y="18" width="70" height="18" rx="9" fill="#e2e8f0"/>
                                <rect x="15" y="5" width="20" height="45" fill="#fbbf24"/>
                                <rect x="35" y="5" width="20" height="45" fill="#38bdf8"/>
                            </g>
                        </svg>
                        <img src="{{ $coverPhoto }}" alt="" class="absolute inset-0 h-full w-full object-cover" loading="lazy" onerror="this.style.display='none'">
                    </div>

                    <div class="px-5 pt-4 pb-5">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="flex min-w-0 flex-1 items-center gap-4">
                                <div class="relative -mt-10 h-14 w-14 shrink-0 overflow-hidden rounded-full border-2 border-white bg-slate-100 shadow-sm">
                                    <svg viewBox="0 0 40 40" class="h-full w-full" aria-hidden="true">
                                        <rect width="40" height="40" fill="#334155"/>
                                        <circle cx="20" cy="15" r="8" fill="#cbd5e1"/>
                                        <path d="M4 40 C4 26 36 26 36 40 Z" fill="#94a3b8"/>
                                    </svg>
                                    <img src="{{ $profilePhoto }}" alt="{{ $artisan->full_name }} photo" class="absolute inset-0 h-full w-full object-cover" loading="lazy" onerror="this.style.display='none'">
                                </div>

                                <div class="min-w-0">
                                    <p class="break-words text-lg font-extrabold leading-tight text-slate-900 [overflow-wrap:anywhere]">
                                        {{ $artisan->full_name }}@if ($age !== null), {{ $age }}@endif
                                    </p>
                                    <p class="mt-1 whitespace-nowrap text-xs font-semibold text-slate-600">
                                        {{ $profession }}{{ filled($profession) ? ' • ' : '' }}{{ $artisan->experienceYears() }}+ years' experience
                                    </p>
                                    <p class="mt-1 break-words text-xs text-slate-500 [overflow-wrap:anywhere]">{{ $address }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <x-share-menu :url="$profileUrl" :title="$artisan->full_name" class="h-9 w-9" />
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
                            <p class="text-[11px] text-slate-400">Joined {{ $artisan->created_at?->format('F Y') }}</p>
                        </div>

                        <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex min-w-[180px] flex-1 items-center gap-3">
                                <span class="text-[11px] font-bold text-slate-700">{{ $satisfaction }}%</span>
                                <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-200">
                                    <div class="h-full bg-sky-500" style="width: {{ $satisfaction }}%"></div>
                                </div>
                            </div>

                            <span class="whitespace-nowrap rounded-full bg-sky-600 px-3 py-2 text-xs font-bold text-white">
                                @if ($artisan->priceLabel() !== null)
                                    {{ $artisan->priceLabel() }}
                                @else
                                    Contact for rate
                                @endif
                            </span>
                            <livewire:like-dislike :artisan="$artisan" />
                        </div>

                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                            <div class="flex flex-wrap gap-2" aria-label="Rating">
                                @foreach ($ratingOptions as $option)
                                    <span class="app-rating-pill {{ $option === $ratingLabel ? 'app-rating-pill-active' : '' }}">{{ $option }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Let's Connect -->
                <div id="connect" class="rounded-2xl border border-slate-200 bg-white p-4 lg:col-span-2" x-data="{ queryOpen: @js($errors->has('phone') || $errors->has('query')), inactiveNotice: false, inactiveNoticeTimer: null, showInactiveNotice() { this.inactiveNotice = true; clearTimeout(this.inactiveNoticeTimer); this.inactiveNoticeTimer = setTimeout(() => { this.inactiveNotice = false; }, 4000); } }">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h2 class="text-lg font-bold text-slate-900">Let&rsquo;s Connect</h2>
                            <p class="mt-1 max-w-[20rem] text-xs leading-snug text-slate-700">
                                Speaks: {{ $languageSummary !== '' ? $languageSummary : 'Languages not provided' }}
                            </p>
                        </div>

                        <div class="flex w-full items-center gap-4 sm:w-auto sm:justify-end">
                            @if ($artisan->is_active && $callHref)
                                <a href="{{ $callHref }}" class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500 text-white shadow-sm transition-colors hover:bg-emerald-600" aria-label="Call">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                </a>
                            @else
                                <button type="button" @if (! $artisan->is_active) @click="showInactiveNotice()" aria-disabled="true" @endif class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500 text-white shadow-sm transition-colors @if ($artisan->is_active) hover:bg-emerald-600 @else cursor-not-allowed opacity-60 @endif" aria-label="{{ $artisan->is_active ? 'Call' : 'Call unavailable' }}">
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

                            <button type="button" class="flex h-12 w-12 items-center justify-center rounded-full bg-rose-600 text-white shadow-sm transition-colors hover:bg-rose-700" aria-label="Send a query" aria-haspopup="dialog" @click="queryOpen = true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                            </button>
                        </div>
                    </div>

                    <div x-show="queryOpen" x-cloak x-transition.opacity.duration.200ms class="fixed inset-0 z-50 flex items-end justify-center bg-slate-900/60 sm:items-center sm:p-6" @click.self="queryOpen = false" @keydown.escape.window="queryOpen = false" role="dialog" aria-modal="true" aria-labelledby="query-title">
                        <div class="w-full rounded-t-2xl bg-white p-5 shadow-xl sm:max-w-md sm:rounded-2xl">
                            <div class="mb-1 flex items-start justify-between gap-3">
                                <div>
                                    <h3 id="query-title" class="text-base font-bold text-slate-900">Send a query</h3>
                                    <p class="mt-1 text-xs leading-snug text-slate-500">Leave your phone number and details. {{ $artisan->full_name }} gets a notification and can call you back.</p>
                                </div>
                                <button type="button" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-slate-200" @click="queryOpen = false" aria-label="Close query form">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/></svg>
                                </button>
                            </div>

                            <form method="POST" action="{{ route('artisan.query.store', $artisan) }}" class="mt-4 flex flex-col gap-3">
                                @csrf

                                <div>
                                    <label for="query-phone" class="text-xs font-bold text-slate-700">Your phone number</label>
                                    <input id="query-phone" name="phone" type="tel" value="{{ old('phone') }}" placeholder="+41 79 000 00 00" required autocomplete="tel" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                                    @error('phone')
                                        <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="query-details" class="text-xs font-bold text-slate-700">Your query</label>
                                    <textarea id="query-details" name="query" rows="4" required placeholder="Explain what you need from {{ $artisan->full_name }}..." class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200">{{ old('query') }}</textarea>
                                    @error('query')
                                        <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="submit" class="w-full rounded-xl bg-rose-600 px-4 py-3 text-sm font-bold text-white transition-colors hover:bg-rose-700">Send query</button>
                            </form>
                        </div>
                    </div>

                    <div x-show="inactiveNotice" x-cloak x-transition.opacity.duration.200ms class="fixed inset-x-4 top-4 z-[60] mx-auto max-w-sm rounded-xl bg-rose-600 px-4 py-3 text-sm font-semibold text-white shadow-lg sm:inset-x-auto sm:right-6 sm:top-6" role="alert" aria-live="assertive">
                        {{ $artisan->full_name }} is not active to receive phone calls.
                    </div>
                </div>

                <!-- Personal statement -->
                <div class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white px-4 py-4">
                    <div class="flex items-center justify-between gap-3">
                        <span class="block text-lg font-bold text-slate-900">Personal Statement</span>
                        @if (filled($artisan->video_cv))
                            <a href="{{ $videoCv }}" class="app-document-icon shrink-0" aria-label="View personal statement attachment">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                    @if ($artisan->biography)
                        <div class="mt-3 break-words text-sm leading-relaxed text-slate-600 [overflow-wrap:anywhere]">{!! $artisan->biography !!}</div>
                    @endif
                </div>

                <!-- Service details -->
                <div id="service-details" class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white p-4">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <h3 class="text-lg font-bold text-slate-900">Service Details</h3>
                        @if ($hasServiceAttachment)
                            <a href="#service-details" class="app-document-icon shrink-0" aria-label="View service attachment">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                                </svg>
                            </a>
                        @endif
                    </div>

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
                        <div class="mb-1">
                            <span class="break-words font-bold text-slate-900 [overflow-wrap:anywhere]">{{ $service->title }}</span>
                        </div>
                        <p class="mb-3 break-words text-sm text-slate-600 [overflow-wrap:anywhere]">{{ $serviceSummary }}</p>
                    @empty
                        <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white/60 px-6 py-10 text-center">
                            <span class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                                </svg>
                            </span>
                            <p class="mt-4 break-words text-sm font-semibold text-slate-700 [overflow-wrap:anywhere]">No service details added!</p>
                        </div>
                    @endforelse
                </div>

            </div>
        </div>
    </div>
</div>

<x-session-message />
<livewire:footer-section />
@endsection
