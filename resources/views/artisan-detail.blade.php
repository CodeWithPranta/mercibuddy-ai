@extends('layouts.landing')
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

        <div class="app-profile-hero">
            <div class="app-profile-cover" style="background-image: url('{{ $coverPhoto }}')"></div>
            <div class="app-profile-head">
                <img src="{{ $profilePhoto }}" alt="{{ $artisan->full_name }} photo" class="app-profile-photo">
                <div class="app-profile-identity">
                    <h1>{{ $artisan->full_name }}</h1>
                    <p class="app-profile-profession">{{ $artisan->profession }}{{ $artisan->profession_type ? ' · '.$artisan->profession_type : '' }}</p>
                    <p class="app-profile-location">{{ $artisan->locationLabel() }}</p>
                    <div class="app-artisan-chips">
                        <span class="app-chip">{{ $artisan->category?->name }}</span>
                        <span class="app-chip app-chip-gold">{{ $artisan->experienceYears() }}+ yrs experience</span>
                    </div>
                </div>
            </div>
            <div class="app-profile-rating">
                <div class="app-artisan-rating">
                    <div class="app-artisan-rating-bar"><div style="width: {{ $artisan->satisfactionPercentage() }}%"></div></div>
                    <span>{{ $artisan->satisfactionPercentage() }}% satisfaction · {{ $artisan->likeCount() }} {{ Str::plural('like', $artisan->likeCount()) }}</span>
                </div>
                <livewire:like-dislike :artisan="$artisan" />
            </div>
        </div>

        <div class="app-profile-stats">
            <div class="app-stat">
                <span class="app-stat-value">{{ $artisan->experienceYears() }}+</span>
                <span class="app-stat-label">Years experience</span>
            </div>
            <div class="app-stat">
                <span class="app-stat-value">{{ $age }}</span>
                <span class="app-stat-label">Years old</span>
            </div>
            <div class="app-stat">
                <span class="app-stat-value">{{ $artisan->satisfactionPercentage() }}%</span>
                <span class="app-stat-label">Satisfaction</span>
            </div>
            <div class="app-stat">
                <span class="app-stat-value">{{ $artisan->created_at?->format('Y') }}</span>
                <span class="app-stat-label">Member since</span>
            </div>
        </div>

        @if ($media !== null)
            <div class="app-profile-card">
                <h2>Get in touch</h2>
                <div class="app-detail-contact">
                    @if ($media->phone)
                        <a href="tel:{{ $media->phone }}" aria-label="Call {{ $artisan->full_name }}"><img src="{{ asset('photos/phone-call.png') }}" alt="Phone" class="w-7 h-7"></a>
                    @endif
                    @if ($media->linkedin)
                        <a href="{{ $media->linkedin }}" target="_blank" rel="noopener" aria-label="LinkedIn profile"><img src="{{ asset('photos/linkedin.png') }}" alt="LinkedIn" class="w-7 h-7"></a>
                    @endif
                    @if ($media->facebook)
                        <a href="{{ $media->facebook }}" target="_blank" rel="noopener" aria-label="Facebook profile"><img src="{{ asset('photos/facebook.png') }}" alt="Facebook" class="w-7 h-7"></a>
                    @endif
                    @if ($media->whatsapp)
                        <a href="https://wa.me/{{ $media->whatsapp }}" target="_blank" rel="noopener" aria-label="WhatsApp chat"><img src="{{ asset('photos/whatsapp.png') }}" alt="WhatsApp" class="w-7 h-7"></a>
                    @endif
                    @if ($media->email)
                        <a href="mailto:{{ $media->email }}" aria-label="Send email"><img src="{{ asset('photos/mail.png') }}" alt="Email" class="w-7 h-7"></a>
                    @endif
                </div>
            </div>
        @endif

        <div class="app-profile-card">
            <h2>Personal Statement</h2>
            <div class="app-detail-bio">{!! $artisan->biography !!}</div>
            @if ($artisan->last_education)
                <p class="app-profile-education">Education: {{ $artisan->last_education }}</p>
            @endif
        </div>

        @if ($services->count() !== 0)
            <div class="app-profile-card">
                <h2>Service Details</h2>
                <div class="app-artisan-results">
                    @foreach ($services as $service)
                        @php
                            $featuredImage = str_starts_with((string) $service->featured_image, 'http')
                                ? $service->featured_image
                                : asset('storage/'.$service->featured_image);
                        @endphp
                        <article class="app-artisan-card">
                            <img src="{{ $featuredImage }}" alt="{{ $service->title }}" class="app-service-image" loading="lazy">
                            <h3>{{ $service->title }}</h3>
                            <div class="app-service-content">{!! $service->content !!}</div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<livewire:footer-section />
@endsection
