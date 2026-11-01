@extends('layouts.landing')
@section('content')
<livewire:navigation />

<div class="app-page">
    <div class="app-page-inner">
        <h1 class="app-page-title">{{ $artisan ? 'Customer queries' : 'Your queries' }}</h1>
        <p class="app-page-lead">
            @if ($artisan)
                {{ $queries->count() }} {{ Str::plural('query', $queries->count()) }} from customers waiting for your call back.
            @else
                Queries you sent to artisans. They get notified as soon as you submit one.
            @endif
        </p>

        @forelse ($queries as $query)
            <article class="app-query-card">
                <div class="app-query-head">
                    <div>
                        <p class="app-query-name">
                            {{ $artisan ? ($query->user?->name ?? 'Customer') : ($query->artisan?->full_name ?? 'Artisan') }}
                        </p>
                        <p class="app-query-meta">{{ $query->created_at?->diffForHumans() }}</p>
                    </div>
                    <a href="tel:{{ $query->phone }}" class="app-query-phone">{{ $query->phone }}</a>
                </div>
                <p class="app-query-body">{{ $query->query }}</p>
                @unless ($artisan)
                    @if ($query->artisan)
                        <a class="app-query-link" href="{{ route('artisan.show', ['artisan' => $query->artisan->id, 'slug' => Str::slug($query->artisan->full_name)]) }}" wire:navigate.hover>View artisan profile</a>
                    @endif
                @endunless
            </article>
        @empty
            <div class="app-page-card">
                <p class="app-page-content">
                    {{ $artisan ? 'No customer queries yet. They will appear here and in the Alerts icon.' : 'You have not sent any queries yet. Open an artisan profile and tap the bell to send one.' }}
                </p>
            </div>
        @endforelse
    </div>
</div>

<x-session-message />
<livewire:footer-section />
@endsection
