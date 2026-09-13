@extends('layouts.landing')
@section('content')

<livewire:navigation />

<div class="app-page">
    <div class="app-page-card app-session-card">
        <div class="app-session-user">
            <span class="app-session-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
            <div class="app-session-identity">
                <p class="app-session-name">{{ auth()->user()->name }}</p>
                <p class="app-session-email">{{ auth()->user()->email }}</p>
                <p class="app-session-status"><span class="app-status-dot" aria-hidden="true"></span>Logged in</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="app-logout-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 8V6a1 1 0 0 0-1-1H6a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h7a1 1 0 0 0 1-1v-2m-5-8 5 5-5 5m5-5H3" /></svg>
                Log out
            </button>
        </form>
    </div>
    @if (in_array(auth()->user()->user_type, [2, 3], true))
        <div class="app-page-card app-workspace-card">
            <div>
                <h2 class="app-workspace-title">Artisan Workspace</h2>
                <p class="app-workspace-text">Manage your services, profile and contact details from your artisan dashboard.</p>
            </div>
            <a href="/artisan" class="app-artisan-link">Open Artisan Dashboard</a>
        </div>
    @endif
    @if (auth()->user()->user_type === 1)
        <div class="app-page-card app-workspace-card">
            <div>
                <h2 class="app-workspace-title">Admin Panel</h2>
                <p class="app-workspace-text">Manage artisans, categories, pages and settings from the admin panel.</p>
            </div>
            <a href="/admin" class="app-artisan-link">Open Admin Panel</a>
        </div>
    @endif
    <div class="app-page-card">
        <h1 class="app-page-title">My Profile</h1>
        <livewire:profile.update-profile-information-form />
        @if (auth()->user()->social_type === NULL)
            <livewire:profile.update-password-form />
        @endif
    </div>
</div>

<livewire:footer-section />
@endsection
