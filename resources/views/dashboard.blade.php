@extends('layouts.landing')
@section('content')

<livewire:navigation />

<div class="app-page">
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
